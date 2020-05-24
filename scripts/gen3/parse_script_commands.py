# Parse the event.inc files from the disassembly project
# to build script command stubs.  This WILL require some manual
# help on the output to handle branching in the command definition

import re
import sys
from typing import Dict, List

infile = open(sys.argv[1], 'r')
outfile = open(sys.argv[2], 'w')

macro_lengths = {
    'map': 2,
}

commands: Dict[str, int] = {}
comments: Dict[str, List[str]] = {}
lengths: Dict[str, int] = {}
comment = []
command = None
value = None
length = 0
for line in infile:
    comment_match = re.match(r'^\s*@ (?P<comment>.+)$', line)
    if comment_match and command is None:
        # Command comment
        comment.append(comment_match.group('comment'))
        continue

    end_match = re.match(r'^\s*\.endm$', line)
    if end_match:
        # Save command
        command_name = command.upper()
        commands[command_name] = value
        comments[command_name] = comment
        lengths[command_name] = length

        # Prepare for new command
        comment = []
        command = None
        value = None
        length = 0
        continue

    command_match = re.match(r'^\s*\.macro (?P<command>\w+) ?(?P<args>.+)?$', line)
    if command_match:
        # New command
        command = command_match.group('command')
        continue

    value_match = re.match(r'^\s*\.(?P<length>\d+)?byte (?P<values>.+)$', line)
    if value_match:
        # Adds parameters.  The first value after defining the macro is always the command value
        if value_match.group('length') is not None:
            byte_length = int(value_match.group('length'))
        else:
            byte_length = 1
        values = value_match.group('values').split(',')
        length += byte_length * len(values)
        if value is None:
            value = int(values[0], 0)

    macro_match = re.match(r'^\s*(?P<macro>{macros}) '.format(macros='|'.join(macro_lengths.keys())), line)
    if macro_match:
        # Macro, add to the length
        length += macro_lengths[macro_match.group('macro')]

# Return of the world's worst encoder
outfile.write('from enum import Enum\nfrom typing import Dict\n\n\nclass ScriptCommand(Enum):\n')
for command_name, command_value in commands.items():
    if command_value is None:
        continue
    for comment in comments[command_name]:
        outfile.write('    # {comment}\n'.format(comment=comment))
    outfile.write('    {name} = bytes.fromhex(\'{value:02X}\')\n\n'.format(name=command_name, value=command_value))
outfile.write('\n\ncommand_lengths: Dict[ScriptCommand, int] = {\n')
for command_name, command_length in lengths.items():
    if command_length == 0:
        continue
    outfile.write('    ScriptCommand.{name}: {length},\n'.format(name=command_name, length=command_length))
outfile.write('}\n')
