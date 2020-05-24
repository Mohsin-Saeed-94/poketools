from enum import Enum
from typing import Dict


class ScriptCommand(Enum):
    # Does nothing.
    NOP = bytes.fromhex('00')

    # Does nothing.
    NOP1 = bytes.fromhex('01')

    # Terminates script execution.
    END = bytes.fromhex('02')

    # Jumps back to after the last-executed call statement, and continues script execution from there.
    RETURN = bytes.fromhex('03')

    # Jumps to destination and continues script execution from there. The location of the calling script is remembered and can be returned to later.
    CALL = bytes.fromhex('04')

    # Jumps to destination and continues script execution from there.
    GOTO = bytes.fromhex('05')

    # If the result of the last comparison matches condition (see Comparison operators), jumps to destination and continues script execution from there.
    GOTO_IF = bytes.fromhex('06')

    # If the result of the last comparison matches condition (see Comparison operators), calls destination.
    CALL_IF = bytes.fromhex('07')

    # Jumps to the standard function at index function.
    GOTOSTD = bytes.fromhex('08')

    # callstd function names
    # Calls the standard function at index function.
    CALLSTD = bytes.fromhex('09')

    # If the result of the last comparison matches condition (see Comparison operators), jumps to the standard function at index function.
    GOTOSTD_IF = bytes.fromhex('0A')

    # If the result of the last comparison matches condition (see Comparison operators), calls the standard function at index function.
    CALLSTD_IF = bytes.fromhex('0B')

    # Executes a script stored in a default RAM location.
    RETURNRAM = bytes.fromhex('0C')

    # Terminates script execution and "resets the script RAM".
    KILLSCRIPT = bytes.fromhex('0D')

    # Sets some status related to Mystery Event.
    SETMYSTERYEVENTSTATUS = bytes.fromhex('0E')

    # Sets the specified script bank to value.
    LOADWORD = bytes.fromhex('0F')

    # Sets the specified script bank to value.
    LOADBYTE = bytes.fromhex('10')

    # Sets the byte at offset to value.
    WRITEBYTETOADDR = bytes.fromhex('11')

    # Copies the byte value at source into the specified script bank.
    LOADBYTEFROMADDR = bytes.fromhex('12')

    # Not sure. Judging from XSE's description I think it takes the least-significant byte in bank source and writes it to destination.
    SETPTRBYTE = bytes.fromhex('13')

    # Copies the contents of bank source into bank destination.
    COPYLOCAL = bytes.fromhex('14')

    # Copies the byte at source to destination, replacing whatever byte was previously there.
    COPYBYTE = bytes.fromhex('15')

    # Changes the value of destination to value.
    SETVAR = bytes.fromhex('16')

    # Changes the value of destination by adding value to it. Overflow is not prevented (0xFFFF + 1 = 0x0000).
    ADDVAR = bytes.fromhex('17')

    # Changes the value of destination by subtracting value to it. Overflow is not prevented (0x0000 - 1 = 0xFFFF).
    SUBVAR = bytes.fromhex('18')

    # Copies the value of source into destination.
    COPYVAR = bytes.fromhex('19')

    # If source is not a variable, then this function acts like setvar. Otherwise, it acts like copyvar.
    SETORCOPYVAR = bytes.fromhex('1A')

    # Compares the values of script banks a and b, after forcing the values to bytes.
    COMPARE_LOCAL_TO_LOCAL = bytes.fromhex('1B')

    # Compares the least-significant byte of the value of script bank a to a fixed byte value (b).
    COMPARE_LOCAL_TO_VALUE = bytes.fromhex('1C')

    # Compares the least-significant byte of the value of script bank a to the byte located at offset b.
    COMPARE_LOCAL_TO_ADDR = bytes.fromhex('1D')

    # Compares the byte located at offset a to the least-significant byte of the value of script bank b.
    COMPARE_ADDR_TO_LOCAL = bytes.fromhex('1E')

    # Compares the byte located at offset a to a fixed byte value (b).
    COMPARE_ADDR_TO_VALUE = bytes.fromhex('1F')

    # Compares the byte located at offset a to the byte located at offset b.
    COMPARE_ADDR_TO_ADDR = bytes.fromhex('20')

    # Compares the value of `var` to a fixed word value (b).
    COMPARE_VAR_TO_VALUE = bytes.fromhex('21')

    # Compares the value of `var1` to the value of `var2`.
    COMPARE_VAR_TO_VAR = bytes.fromhex('22')

    # Calls the native C function stored at `func`.
    CALLNATIVE = bytes.fromhex('23')

    # Replaces the script with the function stored at `func`. Execution returns to the bytecode script when func returns TRUE.
    GOTONATIVE = bytes.fromhex('24')

    # Calls a special function; that is, a function designed for use by scripts and listed in a table of pointers.
    SPECIAL = bytes.fromhex('25')

    # Calls a special function. That function's output (if any) will be written to the variable you specify.
    SPECIALVAR = bytes.fromhex('26')

    # temporary solution
    SPECIALVAR_ = bytes.fromhex('26')

    # Blocks script execution until a command or ASM code manually unblocks it. Generally used with specific commands and specials. If this command runs, and a subsequent command or piece of ASM does not unblock state, the script will remain blocked indefinitely (essentially a hang).
    WAITSTATE = bytes.fromhex('27')

    # Blocks script execution for time (frames? milliseconds?).
    DELAY = bytes.fromhex('28')

    # Sets a to 1.
    SETFLAG = bytes.fromhex('29')

    # Sets a to 0.
    CLEARFLAG = bytes.fromhex('2A')

    # Compares a to 1.
    CHECKFLAG = bytes.fromhex('2B')

    # Initializes the RTC`s local time offset to the given hour and minute. In FireRed, this command is a nop.
    INITCLOCK = bytes.fromhex('2C')

    # Runs time based events. In FireRed, this command is a nop.
    DOTIMEBASEDEVENTS = bytes.fromhex('2D')

    # Sets the values of variables 0x8000, 0x8001, and 0x8002 to the current hour, minute, and second. In FRLG, this command sets those variables to zero.
    GETTIME = bytes.fromhex('2E')

    # Plays the specified (sound_number) sound. Only one sound may play at a time, with newer ones interrupting older ones.
    PLAYSE = bytes.fromhex('2F')

    # Blocks script execution until the currently-playing sound (triggered by playse) finishes playing.
    WAITSE = bytes.fromhex('30')

    # Plays the specified (fanfare_number) fanfare.
    PLAYFANFARE = bytes.fromhex('31')

    # Blocks script execution until all currently-playing fanfares finish.
    WAITFANFARE = bytes.fromhex('32')

    # Plays the specified (song_number) song. The byte is apparently supposed to be 0x00.
    PLAYBGM = bytes.fromhex('33')

    # Saves the specified (song_number) song to be played later.
    SAVEBGM = bytes.fromhex('34')

    # Crossfades the currently-playing song into the map's default song.
    FADEDEFAULTBGM = bytes.fromhex('35')

    # Crossfades the currently-playng song into the specified (song_number) song.
    FADENEWBGM = bytes.fromhex('36')

    # Fades out the currently-playing song.
    FADEOUTBGM = bytes.fromhex('37')

    # Fades the previously-playing song back in.
    FADEINBGM = bytes.fromhex('38')

    # Sends the player to Warp warp on Map bank.map. If the specified warp is 0xFF, then the player will instead be sent to (X, Y) on the map.
    WARP = bytes.fromhex('39')

    # Clone of warp that does not play a sound effect.
    WARPSILENT = bytes.fromhex('3A')

    # Clone of warp that plays a door opening animation before stepping upwards into it.
    WARPDOOR = bytes.fromhex('3B')

    # Warps the player to another map using a hole animation.
    WARPHOLE = bytes.fromhex('3C')

    # Clone of warp that uses a teleport effect. It is apparently only used in R/S/E.
    WARPTELEPORT = bytes.fromhex('3D')

    # Sets the warp destination to be used later.
    SETWARP = bytes.fromhex('3E')

    # Sets the warp destination that a warp to Warp 127 on Map 127.127 will connect to. Useful when a map has warps that need to go to script-controlled locations (i.e. elevators).
    SETDYNAMICWARP = bytes.fromhex('3F')

    # Sets the destination that diving or emerging from a dive will take the player to.
    SETDIVEWARP = bytes.fromhex('40')

    # Sets the destination that falling into a hole will take the player to.
    SETHOLEWARP = bytes.fromhex('41')

    # Retrieves the player's zero-indexed X- and Y-coordinates in the map, and stores them in the specified variables.
    GETPLAYERXY = bytes.fromhex('42')

    # Retrieves the number of Pokemon in the player's party, and stores that number in VAR_RESULT.
    GETPARTYSIZE = bytes.fromhex('43')

    # Attempts to add quantity of item index to the player's Bag. If the player has enough room, the item will be added and VAR_RESULT will be set to TRUE; otherwise, VAR_RESULT is set to FALSE.
    ADDITEM = bytes.fromhex('44')

    # Removes quantity of item index from the player's Bag.
    REMOVEITEM = bytes.fromhex('45')

    # Checks if the player has enough space in their Bag to hold quantity more of item index. Sets VAR_RESULT to TRUE if there is room, or FALSE is there is no room.
    CHECKITEMSPACE = bytes.fromhex('46')

    # Checks if the player has quantity or more of item index in their Bag. Sets VAR_RESULT to TRUE if the player has enough of the item, or FALSE if they have fewer than quantity of the item.
    CHECKITEM = bytes.fromhex('47')

    # Checks which Bag pocket the specified item belongs in, and writes the pocket value (POCKET_*) to VAR_RESULT. This script is used to show the name of the proper Bag pocket when the player receives an item via callstd (simplified to giveitem in XSE).
    CHECKITEMTYPE = bytes.fromhex('48')

    # Adds a quantity amount of item index to the player's PC. Both arguments can be variables.
    ADDPCITEM = bytes.fromhex('49')

    # Checks for quantity amount of item index in the player's PC. Both arguments can be variables.
    CHECKPCITEM = bytes.fromhex('4A')

    # Adds decoration to the player's PC. In FireRed, this command is a nop. (The argument is read, but not used for anything.)
    ADDDECORATION = bytes.fromhex('4B')

    # Removes a decoration from the player's PC. In FireRed, this command is a nop. (The argument is read, but not used for anything.)
    REMOVEDECORATION = bytes.fromhex('4C')

    # Checks for decoration in the player's PC. In FireRed, this command is a nop. (The argument is read, but not used for anything.)
    CHECKDECOR = bytes.fromhex('4D')

    # Checks if the player has enough space in their PC to hold decoration. Sets VAR_RESULT to TRUE if there is room, or FALSE is there is no room. In FireRed, this command is a nop. (The argument is read, but not used for anything.)
    CHECKDECORSPACE = bytes.fromhex('4E')

    # Applies the movement data at movements to the specified (index) Object. Also closes any standard message boxes that are still open.
    # If no map is specified, 1then the current map is used.
    APPLYMOVEMENT = bytes.fromhex('4F')
    APPLYMOVEMENT_MAP = bytes.fromhex('50')

    # Blocks script execution until the movements being applied to the specified (index) Object finish. If the specified Object is 0x0000, then the command will block script execution until all Objects affected by applymovement finish their movements. If the specified Object is not currently being manipulated with applymovement, then this command does nothing.
    # If no map is specified, then the current map is used.
    WAITMOVEMENT = bytes.fromhex('51')
    WAITMOVEMENT_MAP = bytes.fromhex('52')

    # Attempts to hide the specified (index) Object on the specified (map_group, map_num) map, by setting its visibility flag if it has a valid one. If the Object does not have a valid visibility flag, this command does nothing.
    # If no map is specified, then the current map is used.
    REMOVEOBJECT = bytes.fromhex('53')
    REMOVEOBJECT_MAP = bytes.fromhex('54')

    # Unsets the specified (index) Object's visibility flag on the specified (map_group, map_num) map if it has a valid one. If the Object does not have a valid visibility flag, this command does nothing.
    # If no map is specified, then the current map is used.
    ADDOBJECT = bytes.fromhex('55')
    ADDOBJECT_MAP = bytes.fromhex('56')

    # Sets the specified (index) Object's position on the current map.
    SETOBJECTXY = bytes.fromhex('57')

    SHOWOBJECTAT = bytes.fromhex('58')

    HIDEOBJECTAT = bytes.fromhex('59')

    # If the script was called by an Object, then that Object will turn to face toward the metatile that the player is standing on.
    FACEPLAYER = bytes.fromhex('5A')

    TURNOBJECT = bytes.fromhex('5B')

    # If the Trainer flag for Trainer index is not set, this command does absolutely nothing.
    TRAINERBATTLE_SINGLE = bytes.fromhex('5C 00')
    TRAINERBATTLE_CONT_SCRIPT_SILENT = bytes.fromhex('5C 01')
    TRAINERBATTLE_CONT_SCRIPT = bytes.fromhex('5C 02')
    TRAINERBATTLE_SINGLE_NO_INTRO = bytes.fromhex('5C 03')
    TRAINERBATTLE_DOUBLE = bytes.fromhex('5C 04')
    TRAINERBATTLE_REMATCH = bytes.fromhex('5C 05')
    TRAINERBATTLE_CONT_SCRIPT_DOUBLE = bytes.fromhex('5C 06')
    TRAINERBATTLE_REMATCH_DOUBLE = bytes.fromhex('5C 07')
    TRAINERBATTLE_CONT_SCRIPT_DOUBLE_SILENT = bytes.fromhex('5C 08')
    TRAINERBATTLE_BATTLE_PYRAMID = bytes.fromhex('5C 09')
    TRAINERBATTLE_SET_TRAINER_A = bytes.fromhex('5C 0A')
    TRAINERBATTLE_SET_TRAINER_B = bytes.fromhex('5C 0B')
    TRAINERBATTLE_HILL = bytes.fromhex('5C 0C')

    # Starts a trainer battle using the battle information stored in RAM (usually by trainerbattle, which actually calls this command behind-the-scenes), and blocks script execution until the battle finishes.
    TRAINERBATTLEBEGIN = bytes.fromhex('5D')

    # Goes to address after the trainerbattle command (called by the battle functions, see battle_setup.c)
    GOTOPOSTBATTLESCRIPT = bytes.fromhex('5E')

    # Goes to address specified in the trainerbattle command (called by the battle functions, see battle_setup.c)
    GOTOBEATENSCRIPT = bytes.fromhex('5F')

    # Compares Flag (trainer + 0x500) to 1. (If the flag is set, then the trainer has been defeated by the player.)
    CHECKTRAINERFLAG = bytes.fromhex('60')

    # Sets Flag (trainer + 0x500).
    SETTRAINERFLAG = bytes.fromhex('61')

    # Clears Flag (trainer + 0x500).
    CLEARTRAINERFLAG = bytes.fromhex('62')

    SETOBJECTXYPERM = bytes.fromhex('63')

    # Copies a live object event's xy position to its template, so that if the sprite goes off screen, it'll still be there when it comes back on screen.
    COPYOBJECTXYTOPERM = bytes.fromhex('64')

    SETOBJECTMOVEMENTTYPE = bytes.fromhex('65')

    # If a standard message box (or its text) is being drawn on-screen, this command blocks script execution until the box and its text have been fully drawn.
    WAITMESSAGE = bytes.fromhex('66')

    # Starts displaying a standard message box containing the specified text. If text is a pointer, then the string at that offset will be loaded and used. If text is script bank 0, then the value of script bank 0 will be treated as a pointer to the text. (You can use loadpointer to place a string pointer in a script bank.)
    MESSAGE = bytes.fromhex('67')

    # Closes the current message box.
    CLOSEMESSAGE = bytes.fromhex('68')

    # Ceases movement for all Objects on-screen.
    LOCKALL = bytes.fromhex('69')

    # If the script was called by an Object, then that Object's movement will cease.
    LOCK = bytes.fromhex('6A')

    # Resumes normal movement for all Objects on-screen, and closes any standard message boxes that are still open.
    RELEASEALL = bytes.fromhex('6B')

    # If the script was called by an Object, then that Object's movement will resume. This command also closes any standard message boxes that are still open.
    RELEASE = bytes.fromhex('6C')

    # Blocks script execution until the player presses any key.
    WAITBUTTONPRESS = bytes.fromhex('6D')

    # Displays a YES/NO multichoice box at the specified coordinates, and blocks script execution until the user makes a selection. Their selection is stored in VAR_RESULT as NO (0) or YES (1). Pressing B is equivalent to answering NO
    YESNOBOX = bytes.fromhex('6E')

    # Displays a multichoice box from which the user can choose a selection, and blocks script execution until a selection is made. Lists of options are predefined (sMultichoiceLists) and the one to be used is specified with list. If b is set to a non-zero value, then the user will not be allowed to back out of the multichoice with the B button.
    MULTICHOICE = bytes.fromhex('6F')

    # Displays a multichoice box from which the user can choose a selection, and blocks script execution until a selection is made. Lists of options are predefined (sMultichoiceLists) and the one to be used is specified with list. The default argument determines the initial position of the cursor when the box is first opened; it is zero-indexed, and if it is too large, it is treated as 0x00. If b is set to a non-zero value, then the user will not be allowed to back out of the multichoice with the B button.
    MULTICHOICEDEFAULT = bytes.fromhex('70')

    # Displays a multichoice box from which the user can choose a selection, and blocks script execution until a selection is made. Lists of options are predefined (sMultichoiceLists) and the one to be used is specified with list. The per_row argument determines how many list items will be shown on a single row of the box.
    MULTICHOICEGRID = bytes.fromhex('71')

    # Nopped in Emerald.
    DRAWBOX = bytes.fromhex('72')

    # Nopped in Emerald, but still consumes parameters.
    ERASEBOX = bytes.fromhex('73')

    # Nopped in Emerald, but still consumes parameters.
    DRAWBOXTEXT = bytes.fromhex('74')

    # Displays a box containing the front sprite for the specified (species) Pokemon species.
    SHOWMONPIC = bytes.fromhex('75')

    # Hides all boxes displayed with showmonpic.
    HIDEMONPIC = bytes.fromhex('76')

    # Draws an image of the winner of the contest. In FireRed, this command is a nop. (The argument is discarded.)
    SHOWCONTESTWINNER = bytes.fromhex('77')

    # Displays the string at pointer as braille text in a standard message box. The string must be formatted to use braille characters and needs to provide six extra starting characters that are skipped (in RS, these characters determined the box's size and position, but in Emerald these are calculated automatically).
    BRAILLEMESSAGE = bytes.fromhex('78')

    # Gives the player one of the specified (species) Pokemon at level level holding item. The trailing 0s are unused parameters
    GIVEMON = bytes.fromhex('79')

    GIVEEGG = bytes.fromhex('7A')

    SETMONMOVE = bytes.fromhex('7B')

    # Checks if at least one Pokemon in the player's party knows the specified (index) attack. If so, VAR_RESULT is set to the (zero-indexed) slot number of the first Pokemon that knows the move. If not, VAR_RESULT is set to PARTY_SIZE. VAR_0x8004 is also set to this Pokemon's species.
    CHECKPARTYMOVE = bytes.fromhex('7C')

    # Writes the name of the Pokemon at index species to the specified buffer.
    BUFFERSPECIESNAME = bytes.fromhex('7D')

    # Writes the name of the species of the first Pokemon in the player's party to the specified buffer.
    BUFFERLEADMONSPECIESNAME = bytes.fromhex('7E')

    # Writes the nickname of the Pokemon in slot slot (zero-indexed) of the player's party to the specified buffer. If an empty or invalid slot is specified, ten spaces ("") are written to the buffer.
    BUFFERPARTYMONNICK = bytes.fromhex('7F')

    # Writes the name of the item at index item to the specified buffer. If the specified index is larger than the number of items in the game (0x176), the name of item 0 ("????????") is buffered instead.
    BUFFERITEMNAME = bytes.fromhex('80')

    # Writes the name of the decoration at index decoration to the specified buffer. In FireRed, this command is a nop.
    BUFFERDECORATIONNAME = bytes.fromhex('81')

    # Writes the name of the move at index move to the specified buffer.
    BUFFERMOVENAME = bytes.fromhex('82')

    # Converts the value of input to a decimal string, and writes that string to the specified buffer.
    BUFFERNUMBERSTRING = bytes.fromhex('83')

    # Writes the standard string identified by index to the specified buffer. This command has no protections in place at all, so specifying an invalid standard string (e.x. 0x2B) can and usually will cause data corruption.
    BUFFERSTDSTRING = bytes.fromhex('84')

    # Copies the string at offset to the specified buffer.
    BUFFERSTRING = bytes.fromhex('85')

    # Opens the Pokemart system, offering the specified products for sale.
    POKEMART = bytes.fromhex('86')

    # Opens the Pokemart system and treats the list of items as decorations.
    POKEMARTDECORATION = bytes.fromhex('87')

    # Apparent clone of pokemartdecoration.
    POKEMARTDECORATION2 = bytes.fromhex('88')

    # Starts up the slot machine minigame.
    PLAYSLOTMACHINE = bytes.fromhex('89')

    # Sets a berry tree's specific berry and growth stage. In FireRed, this command is a nop.
    SETBERRYTREE = bytes.fromhex('8A')

    # This allows you to choose a Pokemon to use in a contest. In FireRed, this command sets the byte at 0x03000EA8 to 0x01.
    CHOOSECONTESTMON = bytes.fromhex('8B')

    # Starts a contest. In FireRed, this command is a nop.
    STARTCONTEST = bytes.fromhex('8C')

    # Shows the results of a contest. In FireRed, this command is a nop.
    SHOWCONTESTRESULTS = bytes.fromhex('8D')

    # Starts a contest over a link connection. In FireRed, this command is a nop.
    CONTESTLINKTRANSFER = bytes.fromhex('8E')

    # Stores a random integer between 0 and limit in VAR_RESULT.
    RANDOM = bytes.fromhex('8F')

    # If check is 0x00, this command adds value to the player's money.
    ADDMONEY = bytes.fromhex('90')

    # If check is 0x00, this command subtracts value from the player's money.
    REMOVEMONEY = bytes.fromhex('91')

    # If check is 0x00, this command will check if the player has money >= value; VAR_RESULT is set to TRUE if the player has enough money, or FALSE if they do not.
    CHECKMONEY = bytes.fromhex('92')

    # Spawns a secondary box showing how much money the player has.
    SHOWMONEYBOX = bytes.fromhex('93')

    # Hides the secondary box spawned by showmoney. Consumption of the x and y arguments was dummied out.
    HIDEMONEYBOX = bytes.fromhex('94')

    # Updates the secondary box spawned by showmoney. Consumes but does not use arguments.
    UPDATEMONEYBOX = bytes.fromhex('95')

    # Gets the price reduction for the index given. In FireRed, this command is a nop.
    GETPRICEREDUCTION = bytes.fromhex('96')

    # Fades the screen to and from black and white. Modes are FADE_(TO/FROM)_(WHITE/BLACK)
    FADESCREEN = bytes.fromhex('97')

    # Fades the screen to and from black and white. Modes are FADE_(TO/FROM)_(WHITE/BLACK)
    FADESCREENSPEED = bytes.fromhex('98')

    SETFLASHRADIUS = bytes.fromhex('99')

    ANIMATEFLASH = bytes.fromhex('9A')

    MESSAGEAUTOSCROLL = bytes.fromhex('9B')

    # Executes the specified field move animation.
    DOFIELDEFFECT = bytes.fromhex('9C')

    # Sets up the field effect argument argument with the value value.
    SETFIELDEFFECTARGUMENT = bytes.fromhex('9D')

    # Blocks script execution until all playing field move animations complete.
    WAITFIELDEFFECT = bytes.fromhex('9E')

    # Sets which healing place the player will return to if all of the Pokemon in their party faint.
    SETRESPAWN = bytes.fromhex('9F')

    # Checks the player's gender. If male, then MALE (0) is stored in VAR_RESULT. If female, then FEMALE (1) is stored in VAR_RESULT.
    CHECKPLAYERGENDER = bytes.fromhex('A0')

    # Plays the specified (species) Pokemon's cry. You can use waitcry to block script execution until the sound finishes.
    PLAYMONCRY = bytes.fromhex('A1')

    # Changes the metatile at (x, y) on the current map.
    SETMETATILE = bytes.fromhex('A2')

    # Queues a weather change to the default weather for the map.
    RESETWEATHER = bytes.fromhex('A3')

    # Queues a weather change to type weather.
    SETWEATHER = bytes.fromhex('A4')

    # Executes the weather change queued with resetweather or setweather. The current weather will smoothly fade into the queued weather.
    DOWEATHER = bytes.fromhex('A5')

    # This command manages cases in which maps have tiles that change state when stepped on (specifically, cracked/breakable floors).
    SETSTEPCALLBACK = bytes.fromhex('A6')

    SETMAPLAYOUTINDEX = bytes.fromhex('A7')

    SETOBJECTPRIORITY = bytes.fromhex('A8')

    RESETOBJECTPRIORITY = bytes.fromhex('A9')

    CREATEVOBJECT = bytes.fromhex('AA')

    TURNVOBJECT = bytes.fromhex('AB')

    # Opens the door metatile at (X, Y) with an animation.
    OPENDOOR = bytes.fromhex('AC')

    # Closes the door metatile at (X, Y) with an animation.
    CLOSEDOOR = bytes.fromhex('AD')

    # Waits for the door animation started with opendoor or closedoor to finish.
    WAITDOORANIM = bytes.fromhex('AE')

    # Sets the door tile at (x, y) to be open without an animation.
    SETDOOROPEN = bytes.fromhex('AF')

    # Sets the door tile at (x, y) to be closed without an animation.
    SETDOORCLOSED = bytes.fromhex('B0')

    # In Emerald, this command consumes its parameters and does nothing. In FireRed, this command is a nop.
    ADDELEVMENUITEM = bytes.fromhex('B1')

    # In FireRed and Emerald, this command is a nop.
    SHOWELEVMENU = bytes.fromhex('B2')

    CHECKCOINS = bytes.fromhex('B3')

    ADDCOINS = bytes.fromhex('B4')

    REMOVECOINS = bytes.fromhex('B5')

    # Prepares to start a wild battle against a species at Level level holding item. Running this command will not affect normal wild battles. You start the prepared battle with dowildbattle.
    SETWILDBATTLE = bytes.fromhex('B6')

    # Starts a wild battle against the Pokemon generated by setwildbattle. Blocks script execution until the battle finishes.
    DOWILDBATTLE = bytes.fromhex('B7')

    SETVADDRESS = bytes.fromhex('B8')

    VGOTO = bytes.fromhex('B9')

    VCALL = bytes.fromhex('BA')

    VGOTO_IF = bytes.fromhex('BB')

    VCALL_IF = bytes.fromhex('BC')

    VMESSAGE = bytes.fromhex('BD')

    VLOADPTR = bytes.fromhex('BE')

    VBUFFERSTRING = bytes.fromhex('BF')

    # Spawns a secondary box showing how many Coins the player has.
    SHOWCOINSBOX = bytes.fromhex('C0')

    # Hides the secondary box spawned by showcoins. It consumes its arguments but doesn't use them.
    HIDECOINSBOX = bytes.fromhex('C1')

    # Updates the secondary box spawned by showcoins. It consumes its arguments but doesn't use them.
    UPDATECOINSBOX = bytes.fromhex('C2')

    # Increases the value of the specified game stat by 1. The stat's value will not be allowed to exceed 0x00FFFFFF.
    INCREMENTGAMESTAT = bytes.fromhex('C3')

    # Sets the destination that using an Escape Rope or Dig will take the player to.
    SETESCAPEWARP = bytes.fromhex('C4')

    # Blocks script execution until cry finishes.
    WAITMONCRY = bytes.fromhex('C5')

    # Writes the name of the specified (box) PC box to the specified buffer.
    BUFFERBOXNAME = bytes.fromhex('C6')

    # Sets the color of the text in standard message boxes. 0x00 produces blue (male) text, 0x01 produces red (female) text, 0xFF resets the color to the default for the current OW's gender, and all other values produce black text.
    TEXTCOLOR = bytes.fromhex('C7')

    # The exact purpose of this command is unknown, but it is related to the blue help-text box that appears on the bottom of the screen when the Main Menu is opened.
    LOADHELP = bytes.fromhex('C8')

    # The exact purpose of this command is unknown, but it is related to the blue help-text box that appears on the bottom of the screen when the Main Menu is opened.
    UNLOADHELP = bytes.fromhex('C9')

    # After using this command, all standard message boxes will use the signpost frame.
    SIGNMSG = bytes.fromhex('CA')

    # Ends the effects of signmsg, returning message box frames to normal.
    NORMALMSG = bytes.fromhex('CB')

    # Compares the value of a hidden variable to a dword.
    COMPAREHIDDENVAR = bytes.fromhex('CC')

    # Makes the Pokemon in the specified slot of the player's party obedient. It will not randomly disobey orders in battle.
    SETMONOBEDIENT = bytes.fromhex('CD')

    # Checks if the Pokemon in the specified slot of the player's party is obedient. If the Pokemon is disobedient, VAR_RESULT is TRUE. If the Pokemon is obedient (or if the specified slot is empty or invalid), VAR_RESULT is FALSE.
    CHECKMONOBEDIENCE = bytes.fromhex('CE')

    # Depending on factors I haven't managed to understand yet, this command may cause script execution to jump to the offset specified by the pointer at 0x020375C0.
    GOTORAM = bytes.fromhex('CF')

    # Sets worldmapflag to 1. This allows the player to Fly to the corresponding map, if that map has a flightspot.
    SETWORLDMAPFLAG = bytes.fromhex('D0')

    # Clone of warpteleport? It is apparently only used in FR/LG, and only with specials.[source]
    WARPTELEPORT2 = bytes.fromhex('D1')

    # Changes the location where the player caught the Pokemon in the specified slot of their party.
    SETMONMETLOCATION = bytes.fromhex('D2')

    # For the rotating tile puzzles in Mossdeep Gym/Trick House Room 7. Moves the objects on the colored puzzle specified by puzzleNumber one rotation
    MOVEROTATINGTILEOBJECTS = bytes.fromhex('D3')

    # For the rotating tile puzzles in Mossdeep Gym/Trick House Room 7. Updates the facing direction of all objects on the puzzle tiles
    TURNROTATINGTILEOBJECTS = bytes.fromhex('D4')

    # For the rotating tile puzzles in Mossdeep Gym/Trick House Room 7. Allocates memory for the puzzle objects. isTrickHouse is needed to determine which of the two maps the puzzle is on, in order to know where in the tileset the puzzle tiles start. In FireRed, this command is a nop.
    INITROTATINGTILEPUZZLE = bytes.fromhex('D5')

    # For the rotating tile puzzles in Mossdeep Gym/Trick House Room 7. Frees the memory allocated for the puzzle objects.
    FREEROTATINGTILEPUZZLE = bytes.fromhex('D6')

    WARPMOSSDEEPGYM = bytes.fromhex('D7')

    CMDD8 = bytes.fromhex('D8')

    CMDD9 = bytes.fromhex('D9')

    CLOSEBRAILLEMESSAGE = bytes.fromhex('DA')

    MESSAGE3 = bytes.fromhex('DB')

    FADESCREENSWAPBUFFERS = bytes.fromhex('DC')

    BUFFERTRAINERCLASSNAME = bytes.fromhex('DD')

    BUFFERTRAINERNAME = bytes.fromhex('DE')

    POKENAVCALL = bytes.fromhex('DF')

    WARPSOOTOPOLISLEGEND = bytes.fromhex('E0')

    BUFFERCONTESTTYPESTRING = bytes.fromhex('E1')

    # Writes the name of the specified (item) item to the specified buffer. If the specified item is a Berry (0x85 - 0xAE) or Poke Ball (0x4) and if the quantity is 2 or more, the buffered string will be pluralized ("IES" or "S" appended). If the specified item is the Enigma Berry, I have no idea what this command does (but testing showed no pluralization). If the specified index is larger than the number of items in the game (0x176), the name of item 0 ("????????") is buffered instead.
    BUFFERITEMNAMEPLURAL = bytes.fromhex('E2')


command_lengths: Dict[ScriptCommand, int] = {
    ScriptCommand.NOP: 1,
    ScriptCommand.NOP1: 1,
    ScriptCommand.END: 1,
    ScriptCommand.RETURN: 1,
    ScriptCommand.CALL: 5,
    ScriptCommand.GOTO: 5,
    ScriptCommand.GOTO_IF: 6,
    ScriptCommand.CALL_IF: 6,
    ScriptCommand.GOTOSTD: 2,
    ScriptCommand.CALLSTD: 2,
    ScriptCommand.GOTOSTD_IF: 3,
    ScriptCommand.CALLSTD_IF: 3,
    ScriptCommand.RETURNRAM: 1,
    ScriptCommand.KILLSCRIPT: 1,
    ScriptCommand.SETMYSTERYEVENTSTATUS: 2,
    ScriptCommand.LOADWORD: 6,
    ScriptCommand.LOADBYTE: 3,
    ScriptCommand.WRITEBYTETOADDR: 6,
    ScriptCommand.LOADBYTEFROMADDR: 6,
    ScriptCommand.SETPTRBYTE: 6,
    ScriptCommand.COPYLOCAL: 3,
    ScriptCommand.COPYBYTE: 9,
    ScriptCommand.SETVAR: 5,
    ScriptCommand.ADDVAR: 5,
    ScriptCommand.SUBVAR: 5,
    ScriptCommand.COPYVAR: 5,
    ScriptCommand.SETORCOPYVAR: 5,
    ScriptCommand.COMPARE_LOCAL_TO_LOCAL: 3,
    ScriptCommand.COMPARE_LOCAL_TO_VALUE: 3,
    ScriptCommand.COMPARE_LOCAL_TO_ADDR: 6,
    ScriptCommand.COMPARE_ADDR_TO_LOCAL: 6,
    ScriptCommand.COMPARE_ADDR_TO_VALUE: 6,
    ScriptCommand.COMPARE_ADDR_TO_ADDR: 9,
    ScriptCommand.COMPARE_VAR_TO_VALUE: 5,
    ScriptCommand.COMPARE_VAR_TO_VAR: 5,
    ScriptCommand.CALLNATIVE: 5,
    ScriptCommand.GOTONATIVE: 5,
    ScriptCommand.SPECIAL: 3,
    ScriptCommand.SPECIALVAR: 5,
    ScriptCommand.SPECIALVAR_: 5,
    ScriptCommand.WAITSTATE: 1,
    ScriptCommand.DELAY: 3,
    ScriptCommand.SETFLAG: 3,
    ScriptCommand.CLEARFLAG: 3,
    ScriptCommand.CHECKFLAG: 3,
    ScriptCommand.INITCLOCK: 5,
    ScriptCommand.DOTIMEBASEDEVENTS: 1,
    ScriptCommand.GETTIME: 1,
    ScriptCommand.PLAYSE: 3,
    ScriptCommand.WAITSE: 1,
    ScriptCommand.PLAYFANFARE: 3,
    ScriptCommand.WAITFANFARE: 1,
    ScriptCommand.PLAYBGM: 4,
    ScriptCommand.SAVEBGM: 3,
    ScriptCommand.FADEDEFAULTBGM: 1,
    ScriptCommand.FADENEWBGM: 3,
    ScriptCommand.FADEOUTBGM: 2,
    ScriptCommand.FADEINBGM: 2,
    ScriptCommand.WARP: 8,
    ScriptCommand.WARPSILENT: 8,
    ScriptCommand.WARPDOOR: 8,
    ScriptCommand.WARPHOLE: 3,
    ScriptCommand.WARPTELEPORT: 8,
    ScriptCommand.SETWARP: 8,
    ScriptCommand.SETDYNAMICWARP: 8,
    ScriptCommand.SETDIVEWARP: 8,
    ScriptCommand.SETHOLEWARP: 8,
    ScriptCommand.GETPLAYERXY: 5,
    ScriptCommand.GETPARTYSIZE: 1,
    ScriptCommand.ADDITEM: 5,
    ScriptCommand.REMOVEITEM: 5,
    ScriptCommand.CHECKITEMSPACE: 5,
    ScriptCommand.CHECKITEM: 5,
    ScriptCommand.CHECKITEMTYPE: 3,
    ScriptCommand.ADDPCITEM: 5,
    ScriptCommand.CHECKPCITEM: 5,
    ScriptCommand.ADDDECORATION: 3,
    ScriptCommand.REMOVEDECORATION: 3,
    ScriptCommand.CHECKDECOR: 3,
    ScriptCommand.CHECKDECORSPACE: 3,
    ScriptCommand.APPLYMOVEMENT: 7,
    ScriptCommand.APPLYMOVEMENT_MAP: 9,
    ScriptCommand.WAITMOVEMENT: 3,
    ScriptCommand.WAITMOVEMENT_MAP: 5,
    ScriptCommand.REMOVEOBJECT: 3,
    ScriptCommand.REMOVEOBJECT_MAP: 5,
    ScriptCommand.ADDOBJECT: 3,
    ScriptCommand.ADDOBJECT_MAP: 5,
    ScriptCommand.SETOBJECTXY: 7,
    ScriptCommand.SHOWOBJECTAT: 5,
    ScriptCommand.HIDEOBJECTAT: 5,
    ScriptCommand.FACEPLAYER: 1,
    ScriptCommand.TURNOBJECT: 4,
    # Trainer battles have 13 different types.  They start with a 6 byte header,
    # followed by 1-4 pointers.
    ScriptCommand.TRAINERBATTLE_SINGLE: 6 + 8,
    ScriptCommand.TRAINERBATTLE_CONT_SCRIPT_SILENT: 6 + 12,
    ScriptCommand.TRAINERBATTLE_CONT_SCRIPT: 6 + 12,
    ScriptCommand.TRAINERBATTLE_SINGLE_NO_INTRO: 6 + 4,
    ScriptCommand.TRAINERBATTLE_DOUBLE: 6 + 12,
    ScriptCommand.TRAINERBATTLE_REMATCH: 6 + 8,
    ScriptCommand.TRAINERBATTLE_CONT_SCRIPT_DOUBLE: 6 + 16,
    ScriptCommand.TRAINERBATTLE_REMATCH_DOUBLE: 6 + 12,
    ScriptCommand.TRAINERBATTLE_CONT_SCRIPT_DOUBLE_SILENT: 6 + 16,
    ScriptCommand.TRAINERBATTLE_BATTLE_PYRAMID: 6 + 8,
    ScriptCommand.TRAINERBATTLE_SET_TRAINER_A: 6 + 8,
    ScriptCommand.TRAINERBATTLE_SET_TRAINER_B: 6 + 8,
    ScriptCommand.TRAINERBATTLE_HILL: 6 + 8,
    ScriptCommand.TRAINERBATTLEBEGIN: 1,
    ScriptCommand.GOTOPOSTBATTLESCRIPT: 1,
    ScriptCommand.GOTOBEATENSCRIPT: 1,
    ScriptCommand.CHECKTRAINERFLAG: 3,
    ScriptCommand.SETTRAINERFLAG: 3,
    ScriptCommand.CLEARTRAINERFLAG: 3,
    ScriptCommand.SETOBJECTXYPERM: 7,
    ScriptCommand.COPYOBJECTXYTOPERM: 3,
    ScriptCommand.SETOBJECTMOVEMENTTYPE: 4,
    ScriptCommand.WAITMESSAGE: 1,
    ScriptCommand.MESSAGE: 5,
    ScriptCommand.CLOSEMESSAGE: 1,
    ScriptCommand.LOCKALL: 1,
    ScriptCommand.LOCK: 1,
    ScriptCommand.RELEASEALL: 1,
    ScriptCommand.RELEASE: 1,
    ScriptCommand.WAITBUTTONPRESS: 1,
    ScriptCommand.YESNOBOX: 3,
    ScriptCommand.MULTICHOICE: 5,
    ScriptCommand.MULTICHOICEDEFAULT: 6,
    ScriptCommand.MULTICHOICEGRID: 6,
    ScriptCommand.DRAWBOX: 1,
    ScriptCommand.ERASEBOX: 5,
    ScriptCommand.DRAWBOXTEXT: 5,
    ScriptCommand.SHOWMONPIC: 5,
    ScriptCommand.HIDEMONPIC: 1,
    ScriptCommand.SHOWCONTESTWINNER: 2,
    ScriptCommand.BRAILLEMESSAGE: 5,
    ScriptCommand.GIVEMON: 15,
    ScriptCommand.GIVEEGG: 3,
    ScriptCommand.SETMONMOVE: 5,
    ScriptCommand.CHECKPARTYMOVE: 3,
    ScriptCommand.BUFFERSPECIESNAME: 4,
    ScriptCommand.BUFFERLEADMONSPECIESNAME: 2,
    ScriptCommand.BUFFERPARTYMONNICK: 4,
    ScriptCommand.BUFFERITEMNAME: 4,
    ScriptCommand.BUFFERDECORATIONNAME: 4,
    ScriptCommand.BUFFERMOVENAME: 4,
    ScriptCommand.BUFFERNUMBERSTRING: 4,
    ScriptCommand.BUFFERSTDSTRING: 4,
    ScriptCommand.BUFFERSTRING: 6,
    ScriptCommand.POKEMART: 5,
    ScriptCommand.POKEMARTDECORATION: 5,
    ScriptCommand.POKEMARTDECORATION2: 5,
    ScriptCommand.PLAYSLOTMACHINE: 3,
    ScriptCommand.SETBERRYTREE: 4,
    ScriptCommand.CHOOSECONTESTMON: 1,
    ScriptCommand.STARTCONTEST: 1,
    ScriptCommand.SHOWCONTESTRESULTS: 1,
    ScriptCommand.CONTESTLINKTRANSFER: 1,
    ScriptCommand.RANDOM: 3,
    ScriptCommand.ADDMONEY: 6,
    ScriptCommand.REMOVEMONEY: 6,
    ScriptCommand.CHECKMONEY: 6,
    ScriptCommand.SHOWMONEYBOX: 4,
    ScriptCommand.HIDEMONEYBOX: 3,
    ScriptCommand.UPDATEMONEYBOX: 4,
    ScriptCommand.GETPRICEREDUCTION: 3,
    ScriptCommand.FADESCREEN: 2,
    ScriptCommand.FADESCREENSPEED: 3,
    ScriptCommand.SETFLASHRADIUS: 3,
    ScriptCommand.ANIMATEFLASH: 2,
    ScriptCommand.MESSAGEAUTOSCROLL: 5,
    ScriptCommand.DOFIELDEFFECT: 3,
    ScriptCommand.SETFIELDEFFECTARGUMENT: 4,
    ScriptCommand.WAITFIELDEFFECT: 3,
    ScriptCommand.SETRESPAWN: 3,
    ScriptCommand.CHECKPLAYERGENDER: 1,
    ScriptCommand.PLAYMONCRY: 5,
    ScriptCommand.SETMETATILE: 9,
    ScriptCommand.RESETWEATHER: 1,
    ScriptCommand.SETWEATHER: 3,
    ScriptCommand.DOWEATHER: 1,
    ScriptCommand.SETSTEPCALLBACK: 2,
    ScriptCommand.SETMAPLAYOUTINDEX: 3,
    ScriptCommand.SETOBJECTPRIORITY: 6,
    ScriptCommand.RESETOBJECTPRIORITY: 5,
    ScriptCommand.CREATEVOBJECT: 9,
    ScriptCommand.TURNVOBJECT: 3,
    ScriptCommand.OPENDOOR: 5,
    ScriptCommand.CLOSEDOOR: 5,
    ScriptCommand.WAITDOORANIM: 1,
    ScriptCommand.SETDOOROPEN: 5,
    ScriptCommand.SETDOORCLOSED: 5,
    ScriptCommand.ADDELEVMENUITEM: 8,
    ScriptCommand.SHOWELEVMENU: 1,
    ScriptCommand.CHECKCOINS: 3,
    ScriptCommand.ADDCOINS: 3,
    ScriptCommand.REMOVECOINS: 3,
    ScriptCommand.SETWILDBATTLE: 6,
    ScriptCommand.DOWILDBATTLE: 1,
    ScriptCommand.SETVADDRESS: 5,
    ScriptCommand.VGOTO: 5,
    ScriptCommand.VCALL: 5,
    ScriptCommand.VGOTO_IF: 6,
    ScriptCommand.VCALL_IF: 6,
    ScriptCommand.VMESSAGE: 5,
    ScriptCommand.VLOADPTR: 5,
    ScriptCommand.VBUFFERSTRING: 6,
    ScriptCommand.SHOWCOINSBOX: 3,
    ScriptCommand.HIDECOINSBOX: 3,
    ScriptCommand.UPDATECOINSBOX: 3,
    ScriptCommand.INCREMENTGAMESTAT: 2,
    ScriptCommand.SETESCAPEWARP: 8,
    ScriptCommand.WAITMONCRY: 1,
    ScriptCommand.BUFFERBOXNAME: 4,
    ScriptCommand.TEXTCOLOR: 2,
    ScriptCommand.LOADHELP: 5,
    ScriptCommand.UNLOADHELP: 1,
    ScriptCommand.SIGNMSG: 1,
    ScriptCommand.NORMALMSG: 1,
    ScriptCommand.COMPAREHIDDENVAR: 6,
    ScriptCommand.SETMONOBEDIENT: 3,
    ScriptCommand.CHECKMONOBEDIENCE: 3,
    ScriptCommand.GOTORAM: 1,
    ScriptCommand.SETWORLDMAPFLAG: 3,
    ScriptCommand.WARPTELEPORT2: 8,
    ScriptCommand.SETMONMETLOCATION: 4,
    ScriptCommand.MOVEROTATINGTILEOBJECTS: 3,
    ScriptCommand.TURNROTATINGTILEOBJECTS: 1,
    ScriptCommand.INITROTATINGTILEPUZZLE: 3,
    ScriptCommand.FREEROTATINGTILEPUZZLE: 1,
    ScriptCommand.WARPMOSSDEEPGYM: 8,
    ScriptCommand.CMDD8: 1,
    ScriptCommand.CMDD9: 1,
    ScriptCommand.CLOSEBRAILLEMESSAGE: 1,
    ScriptCommand.MESSAGE3: 5,
    ScriptCommand.FADESCREENSWAPBUFFERS: 2,
    ScriptCommand.BUFFERTRAINERCLASSNAME: 4,
    ScriptCommand.BUFFERTRAINERNAME: 4,
    ScriptCommand.POKENAVCALL: 5,
    ScriptCommand.WARPSOOTOPOLISLEGEND: 8,
    ScriptCommand.BUFFERCONTESTTYPESTRING: 4,
    ScriptCommand.BUFFERITEMNAMEPLURAL: 6,
}
