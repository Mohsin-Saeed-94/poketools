from enum import Enum


class Version(Enum):
    RUBY = 'ruby'
    SAPPHIRE = 'sapphire'
    EMERALD = 'emerald'
    FIRERED = 'firered'
    LEAFGREEN = 'leafgreen'


class VersionGroup(Enum):
    RUBY_SAPPHIRE = 'ruby-sapphire'
    EMERALD = 'emerald'
    FIRERED_LEAFGREEN = 'firered-leafgreen'
