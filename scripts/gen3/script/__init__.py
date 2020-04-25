import io
from io import BufferedReader

from inc import gba
from ..enums import VersionGroup

jumped_to = set()


def do_script(version_group: VersionGroup, rom: BufferedReader, callbacks: dict):
    """
    Evaluate a script
    :param version_group:
    :param rom:
    :param callbacks: This dict should be keyed by ScriptCommand and have a callable as the value.
        The callback will receive the commands argument bytes as the first parameter and the rom
        positioned after the command as the second.  Commands not in this dict will be skipped.
        If the callback seeks the rom, it must return the rom's position to where it was at the
        beginning of the callback.
    :return:
    """
    if version_group == VersionGroup.RUBY_SAPPHIRE:
        from gen3.script.rs_commands import ScriptCommand, command_lengths
    elif version_group == VersionGroup.EMERALD:
        from gen3.script.e_commands import ScriptCommand, command_lengths
    else:
        from gen3.script.frlg_commands import ScriptCommand, command_lengths
    # The max length of the command start code.  Most commands are 1 byte, but some are 2.
    max_command_length = max([len(command.value) for command in ScriptCommand])

    def _get_command(check: bytes) -> ScriptCommand:
        for command in ScriptCommand:
            if command.value == check[:len(command.value)]:
                return command
        raise Exception('Invalid command at {position}: {command}'.format(position=hex(rom.tell()),
                                                                          command=rom.peek(max_command_length).hex()))

    def _call(pointer: bytes, command_rom: BufferedReader):
        """
        Jumps to destination and continues script execution from there. The location of the calling script is
        remembered and can be returned to later.
        :param pointer:
        """
        _goto(pointer, command_rom)

    def _call_if(args: bytes, command_rom: BufferedReader):
        """
        If the result of the last comparison matches condition, calls destination.
        For this purpose the condition is ignored, i.e. always true.
        :param args:
        """
        _call(args[1:], command_rom)

    def _goto(pointer: bytes, command_rom: BufferedReader):
        """
        Jumps to destination and continues script execution from there.
        :param pointer:
        """
        old_position = command_rom.tell()
        new_position = gba.address_from_pointer(pointer)
        if new_position in jumped_to:
            # We've been here before... avoid infinite loops
            return
        jumped_to.add(new_position)
        command_rom.seek(new_position)
        do_script(version_group, command_rom, callbacks)
        command_rom.seek(old_position)

    def _goto_if(args: bytes, command_rom: BufferedReader):
        """
        If the result of the last comparison matches condition, jumps to destination and continues script execution
        from there.

        For this purpose the condition is ignored, i.e. always true.
        :param args:
        """
        _goto(args[1:], command_rom)

    # These handlers are important to script execution.
    callbacks.update({
        ScriptCommand.CALL: _call,
        ScriptCommand.CALL_IF: _call_if,
        ScriptCommand.GOTO: _goto,
        ScriptCommand.GOTO_IF: _goto_if,
    })

    stoppers = [ScriptCommand.END.value, ScriptCommand.RETURN.value]
    while rom.peek(1)[0].to_bytes(1, 'little') not in stoppers:
        command = _get_command(rom.peek(max_command_length))
        if command in callbacks:
            # Call the callback and pass the command's arg bytes
            rom.seek(len(command.value), io.SEEK_CUR)
            callbacks[command](rom.read(command_lengths[command] - len(command.value)), rom)
        else:
            # Seek past the unknown command
            rom.seek(command_lengths[command], io.SEEK_CUR)
    # Advance past the script
    rom.seek(1, io.SEEK_CUR)
