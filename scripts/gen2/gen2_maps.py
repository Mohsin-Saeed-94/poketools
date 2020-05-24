from typing import Dict, List, Union


def get_maps(version_group: str) -> Dict[int, Dict[int, Dict[str, Union[str, List[str]]]]]:
    """
    A map:
    - first level key: group id
    - second level key: map id
    - second level value: a dict with the keys "location" and "area".  Area may be a list
      of areas, if this is the case map properties should apply to all of those areas.

    :param version_group:
    :return:
    """

    def _reorder_maps(maps: dict) -> dict:
        reordered = {}
        map_id = 0x01
        for map_data in maps.values():
            reordered[map_id] = map_data
            map_id += 1
        return reordered

    map_slugs = {}
    map_slugs[0x01] = {
        0x01: {'location': 'olivine-city', 'area': 'pokemon-center'},
        0x02: {'location': 'olivine-city', 'area': 'gym'},
        0x03: {'location': 'olivine-city', 'area': 'tims-house'},
        0x04: {'location': 'olivine-city', 'area': 'beta-house'},
        0x05: {'location': 'olivine-city', 'area': 'punishment-speech-house'},
        0x06: {'location': 'olivine-city', 'area': 'fishing-guru'},
        0x07: {'location': 'olivine-city', 'area': 'cafe'},
        0x08: {'location': 'olivine-city', 'area': 'mart'},
        0x09: {'location': 'johto-route-38', 'area': 'east-gate'},
        0x0A: {'location': 'johto-route-39', 'area': 'barn'},
        0x0B: {'location': 'johto-route-39', 'area': 'farmhouse'},
        0x0C: {'location': 'johto-route-38', 'area': 'whole-area'},
        0x0D: {'location': 'johto-route-39', 'area': 'whole-area'},
        0x0E: {'location': 'olivine-city', 'area': 'whole-area'},
    }
    map_slugs[0x02] = {
        0x01: {'location': 'mahogany-town', 'area': 'red-gyarados-speech-house'},
        0x02: {'location': 'mahogany-town', 'area': 'gym'},
        0x03: {'location': 'mahogany-town', 'area': 'pokemon-center'},
        0x04: {'location': 'johto-route-42', 'area': 'west-gate'},
        0x05: {'location': 'johto-route-42', 'area': 'whole-area'},
        0x06: {'location': 'johto-route-44', 'area': 'whole-area'},
        0x07: {'location': 'mahogany-town', 'area': 'whole-area'},
    }
    map_slugs[0x03] = {
        0x01: {'location': 'sprout-tower', 'area': '1f'},
        0x02: {'location': 'sprout-tower', 'area': '2f'},
        0x03: {'location': 'sprout-tower', 'area': '3f'},
        0x04: {'location': 'tin-tower', 'area': '1f'},
        0x05: {'location': 'tin-tower', 'area': '2f'},
        0x06: {'location': 'tin-tower', 'area': '3f'},
        0x07: {'location': 'tin-tower', 'area': '4f'},
        0x08: {'location': 'tin-tower', 'area': '5f'},
        0x09: {'location': 'tin-tower', 'area': '6f'},
        0x0A: {'location': 'tin-tower', 'area': '7f'},
        0x0B: {'location': 'tin-tower', 'area': '8f'},
        0x0C: {'location': 'tin-tower', 'area': '9f'},
        0x0D: {'location': 'burned-tower', 'area': '1f'},
        0x0E: {'location': 'burned-tower', 'area': 'b1f'},
        0x0F: {'location': 'national-park', 'area': 'whole-area'},
        0x10: {'location': 'national-park', 'area': 'during-bug-contest'},
        0x11: {'location': 'goldenrod-radio-tower', 'area': '1f'},
        0x12: {'location': 'goldenrod-radio-tower', 'area': '2f'},
        0x13: {'location': 'goldenrod-radio-tower', 'area': '3f'},
        0x14: {'location': 'goldenrod-radio-tower', 'area': '45'},
        0x15: {'location': 'goldenrod-radio-tower', 'area': '5f'},
        0x16: {'location': 'ruins-of-alph', 'area': 'outside'},
        0x17: {'location': 'ruins-of-alph', 'area': 'ho-oh-chamber'},
        0x18: {'location': 'ruins-of-alph', 'area': 'kabuto-chamber'},
        0x19: {'location': 'ruins-of-alph', 'area': 'omanyte-chamber'},
        0x1A: {'location': 'ruins-of-alph', 'area': 'aerodactyl-chamber'},
        0x1B: {'location': 'ruins-of-alph', 'area': 'inner-chamber'},
        0x1C: {'location': 'ruins-of-alph', 'area': 'research-center'},
        # 0x1D - 0x24 don't exist in Gold/Silver
        0x1D: {'location': 'ruins-of-alph', 'area': 'ho-oh-item-room'},
        0x1E: {'location': 'ruins-of-alph', 'area': 'kabuto-item-room'},
        0x1F: {'location': 'ruins-of-alph', 'area': 'omanyte-item-room'},
        0x20: {'location': 'ruins-of-alph', 'area': 'aerodactyl-item-room'},
        0x21: {'location': 'ruins-of-alph', 'area': 'ho-oh-word-room'},
        0x22: {'location': 'ruins-of-alph', 'area': 'kabuto-word-rooma'},
        0x23: {'location': 'ruins-of-alph', 'area': 'omanyte-word-room'},
        0x24: {'location': 'ruins-of-alph', 'area': 'whole-area'},
        0x25: {'location': 'union-cave', 'area': '1f'},
        0x26: {'location': 'union-cave', 'area': 'b1f'},
        0x27: {'location': 'union-cave', 'area': 'b2f'},
        0x28: {'location': 'slowpoke-well', 'area': 'b1f'},
        0x29: {'location': 'slowpoke-well', 'area': 'b2f'},
        0x2A: {'location': 'olivine-lighthouse', 'area': '1f'},
        0x2B: {'location': 'olivine-lighthouse', 'area': '2f'},
        0x2C: {'location': 'olivine-lighthouse', 'area': '3f'},
        0x2D: {'location': 'olivine-lighthouse', 'area': '4f'},
        0x2E: {'location': 'olivine-lighthouse', 'area': '5f'},
        0x2F: {'location': 'olivine-lighthouse', 'area': '6f'},
        0x30: {'location': 'mahogany-town', 'area': 'mart'},
        0x31: {'location': 'team-rocket-hq', 'area': 'b1f'},
        0x32: {'location': 'team-rocket-hq', 'area': 'b2f'},
        0x33: {'location': 'team-rocket-hq', 'area': 'b3f'},
        0x34: {'location': 'ilex-forest', 'area': 'whole-area'},
        0x35: {'location': 'goldenrod-underground', 'area': 'whole-area'},
        0x36: {'location': 'goldenrod-underground', 'area': 'switch-room'},
        0x37: {'location': 'goldenrod-city', 'area': 'department-store/b1f'},
        0x38: {'location': 'goldenrod-underground', 'area': 'warehouse'},
        0x39: {'location': 'mt-mortar', 'area': '1f'},
        0x3A: {'location': 'mt-mortar', 'area': 'lower-cave'},
        0x3B: {'location': 'mt-mortar', 'area': 'upper-cave'},
        0x3C: {'location': 'mt-mortar', 'area': 'b1f'},
        0x3D: {'location': 'ice-path', 'area': '1f'},
        0x3E: {'location': 'ice-path', 'area': 'b1f'},
        0x3F: {'location': 'ice-path', 'area': 'b2f-ice-sheet'},
        0x40: {'location': 'ice-path', 'area': 'b2f'},
        0x41: {'location': 'ice-path', 'area': 'b3f'},
        0x42: {'location': 'whirl-islands', 'area': '1f-nw'},
        0x43: {'location': 'whirl-islands', 'area': '1f-ne'},
        0x44: {'location': 'whirl-islands', 'area': '1f-sw'},
        0x45: {'location': 'whirl-islands', 'area': 'b1f-cave'},
        0x46: {'location': 'whirl-islands', 'area': '1f-se'},
        0x47: {'location': 'whirl-islands', 'area': 'b1f'},
        0x48: {'location': 'whirl-islands', 'area': 'b2f'},
        0x49: {'location': 'whirl-islands', 'area': 'b2f-lugia'},
        0x4A: {'location': 'silver-cave', 'area': '1f-front'},
        0x4B: {'location': 'silver-cave', 'area': '1f-back'},
        0x4C: {'location': 'silver-cave', 'area': 'summit'},
        0x4D: {'location': 'silver-cave', 'area': '1f-item-caves'},
        0x4E: {'location': 'dark-cave', 'area': 'violet-city-entrance'},
        0x4F: {'location': 'dark-cave', 'area': 'blackthorn-city-entrance'},
        0x50: {'location': 'dragons-den', 'area': 'entrance'},
        0x51: {'location': 'dragons-den', 'area': 'cavern'},
        # 0x52 isn't in Gold/Silver
        0x52: {'location': 'dragons-den', 'area': 'shrine'},
        0x53: {'location': 'tohjo-falls', 'area': 'whole-area'},
        0x54: {'location': 'digletts-cave', 'area': ['underground', 'route-2', 'route-11']},
        0x55: {'location': 'mt-moon', 'area': 'cave'},
        0x56: {'location': 'kanto-underground-path-5-6', 'area': 'whole-area'},
        0x57: {'location': 'rock-tunnel', 'area': '1f'},
        0x58: {'location': 'rock-tunnel', 'area': 'b1f'},
        0x59: {'location': 'safari-zone-fuchsia-gate-beta', 'area': 'whole-area'},
        0x5A: {'location': 'safari-zone-beta', 'area': 'whole-area'},
        0x5B: {'location': 'kanto-victory-road', 'area': ['1f', '2f', '3f']},
    }
    if version_group == 'gold-silver':
        for map_id in range(0x1D, 0x25):
            del map_slugs[0x03][map_id]
        del map_slugs[0x03][0x52]
        map_slugs[0x03] = _reorder_maps(map_slugs[0x03])
    map_slugs[0x04] = {
        0x01: {'location': 'ecruteak-city', 'area': ['tin-tower-gate-front', 'tin-tower-gate-b1f']},
        0x02: {'location': 'ecruteak-city', 'area': 'tin-tower-gate-back'},
        0x03: {'location': 'ecruteak-city', 'area': 'pokemon-center'},
        0x04: {'location': 'ecruteak-city', 'area': 'lugia-speech-house'},
        0x05: {'location': 'ecruteak-city', 'area': 'dance-theatre'},
        0x06: {'location': 'ecruteak-city', 'area': 'mart'},
        0x07: {'location': 'ecruteak-city', 'area': 'gym'},
        0x08: {'location': 'ecruteak-city', 'area': 'itemfinder-house'},
        0x09: {'location': 'ecruteak-city', 'area': 'whole-area'},
    }
    map_slugs[0x05] = {
        0x01: {'location': 'blackthorn-city', 'area': 'gym-1f'},
        0x02: {'location': 'blackthorn-city', 'area': 'gym-2f'},
        0x03: {'location': 'blackthorn-city', 'area': 'dragon-speech-house'},
        0x04: {'location': 'blackthorn-city', 'area': 'emys-house'},
        0x05: {'location': 'blackthorn-city', 'area': 'mart'},
        0x06: {'location': 'blackthorn-city', 'area': 'pokemon-center'},
        0x07: {'location': 'blackthorn-city', 'area': 'move-deleter'},
        0x08: {'location': 'johto-route-45', 'area': 'whole-area'},
        0x09: {'location': 'johto-route-46', 'area': 'whole-area'},
        0x0A: {'location': 'blackthorn-city', 'area': 'whole-area'},
    }
    map_slugs[0x06] = {
        0x01: {'location': 'cinnabar-island', 'area': 'pokemon-center'},
        0x02: {'location': 'cinnabar-pokecenter-2f-beta', 'area': 'whole-area'},
        0x03: {'location': 'fuchsia-city', 'area': 'south-gate'},
        0x04: {'location': 'cinnabar-island', 'area': 'gym'},
        0x05: {'location': 'kanto-route-19', 'area': 'whole-area'},
        0x06: {'location': 'kanto-route-20', 'area': 'whole-area'},
        0x07: {'location': 'kanto-route-21', 'area': 'whole-area'},
        0x08: {'location': 'cinnabar-island', 'area': 'whole-area'},
    }
    map_slugs[0x07] = {
        0x01: {'location': 'cerulean-city', 'area': 'badge-mans-house'},
        0x02: {'location': 'cerulean-city', 'area': 'police-station'},
        0x03: {'location': 'cerulean-city', 'area': 'trade-speech-house'},
        0x04: {'location': 'cerulean-city', 'area': 'whole-area'},
        0x05: {'location': 'cerulean-pokecenter-2f-beta', 'area': 'whole-area'},
        0x06: {'location': 'cerulean-city', 'area': 'gym'},
        0x07: {'location': 'cerulean-city', 'area': 'mart'},
        0x08: {'location': 'kanto-route-10', 'area': 'pokemon-center'},
        0x09: {'location': 'route-10-pokecenter-2f-beta', 'area': 'whole-area'},
        0x0A: {'location': 'power-plant', 'area': 'whole-area'},
        0x0B: {'location': 'kanto-route-25', 'area': 'bills-house'},
        0x0C: {'location': 'kanto-route-4', 'area': 'whole-area'},
        0x0D: {'location': 'kanto-route-9', 'area': 'whole-area'},
        0x0E: {'location': 'kanto-route-10', 'area': 'cerulean-city'},
        0x0F: {'location': 'kanto-route-24', 'area': 'whole-area'},
        0x10: {'location': 'kanto-route-25', 'area': 'whole-area'},
        0x11: {'location': 'cerulean-city', 'area': 'whole-area'},
    }
    map_slugs[0x08] = {
        0x01: {'location': 'azalea-town', 'area': 'pokemon-center'},
        0x02: {'location': 'azalea-town', 'area': 'charcoal-kiln'},
        0x03: {'location': 'azalea-town', 'area': 'mart'},
        0x04: {'location': 'azalea-town', 'area': 'kurts-house'},
        0x05: {'location': 'azalea-town', 'area': 'gym'},
        0x06: {'location': 'johto-route-33', 'area': 'whole-area'},
        0x07: {'location': 'azalea-town', 'area': 'whole-area'},
    }
    map_slugs[0x09] = {
        0x01: {'location': 'lake-of-rage', 'area': 'hidden-power-house'},
        0x02: {'location': 'lake-of-rage', 'area': 'magikarp-house'},
        0x03: {'location': 'johto-route-43', 'area': 'mahogany-gate'},
        0x04: {'location': 'johto-route-43', 'area': 'lake-gate'},
        0x05: {'location': 'johto-route-43', 'area': 'whole-area'},
        0x06: {'location': 'lake-of-rage', 'area': 'whole-area'},
    }
    map_slugs[0x0A] = {
        0x01: {'location': 'johto-route-32', 'area': 'whole-area'},
        0x02: {'location': 'johto-route-35', 'area': 'whole-area'},
        0x03: {'location': 'johto-route-36', 'area': 'whole-area'},
        0x04: {'location': 'johto-route-37', 'area': 'whole-area'},
        0x05: {'location': 'violet-city', 'area': 'whole-area'},
        0x06: {'location': 'violet-city', 'area': 'mart'},
        0x07: {'location': 'violet-city', 'area': 'gym'},
        0x08: {'location': 'violet-city', 'area': 'pokemon-academy'},
        0x09: {'location': 'violet-city', 'area': 'nickname-speech-house'},
        0x0A: {'location': 'violet-city', 'area': 'pokemon-center'},
        0x0B: {'location': 'violet-city', 'area': 'kyles-house'},
        0x0C: {'location': 'johto-route-32', 'area': 'ruins-of-alph-gate'},
        0x0D: {'location': 'johto-route-32', 'area': 'pokemon-center'},
        0x0E: {'location': 'johto-route-35', 'area': 'south-gate'},
        0x0F: {'location': 'johto-route-35', 'area': 'north-gate'},
        0x10: {'location': 'johto-route-36', 'area': 'south-gate'},
        0x11: {'location': 'johto-route-36', 'area': 'west-gate'},
    }
    # This bank was shuffled around in Crystal
    if version_group == 'gold-silver':
        map_slugs[0x0B] = {
            0x01: {'location': 'johto-route-34', 'area': 'whole-area'},
            0x02: {'location': 'goldenrod-city', 'area': 'whole-area'},
            0x03: {'location': 'goldenrod-city', 'area': 'gym'},
            0x04: {'location': 'goldenrod-city', 'area': 'bike-shop'},
            0x05: {'location': 'goldenrod-city', 'area': 'happiness-rater'},
            0x06: {'location': 'goldenrod-city', 'area': 'bills-familys-house'},
            0x07: {'location': 'goldenrod-city', 'area': 'train-station'},
            0x08: {'location': 'goldenrod-city', 'area': 'flower-shop'},
            0x09: {'location': 'goldenrod-city', 'area': 'pokemon-center'},
            0x0A: {'location': 'goldenrod-city', 'area': 'pp-speech-house'},
            0x0B: {'location': 'goldenrod-city', 'area': 'name-rater'},
            0x0C: {'location': 'goldenrod-city', 'area': 'department-store/1f'},
            0x0D: {'location': 'goldenrod-city', 'area': 'department-store/2f'},
            0x0E: {'location': 'goldenrod-city', 'area': 'department-store/3f'},
            0x0F: {'location': 'goldenrod-city', 'area': 'department-store/4f'},
            0x10: {'location': 'goldenrod-city', 'area': 'department-store/5f'},
            0x11: {'location': 'goldenrod-city', 'area': 'department-store/6f'},
            0x12: {'location': 'goldenrod-city', 'area': 'department-store/elevator'},
            0x13: {'location': 'goldenrod-city', 'area': 'game-corner'},
            0x14: {'location': 'ilex-forest', 'area': 'azalea-gate'},
            0x15: {'location': 'johto-route-34', 'area': 'ilex-forest-gate'},
            0x16: {'location': 'johto-route-34', 'area': 'day-care'},
        }
    else:
        map_slugs[0x0B] = {
            0x01: {'location': 'johto-route-34', 'area': 'whole-area'},
            0x02: {'location': 'goldenrod-city', 'area': 'whole-area'},
            0x03: {'location': 'goldenrod-city', 'area': 'gym'},
            0x04: {'location': 'goldenrod-city', 'area': 'bike-shop'},
            0x05: {'location': 'goldenrod-city', 'area': 'happiness-rater'},
            0x06: {'location': 'goldenrod-city', 'area': 'bills-familys-house'},
            0x07: {'location': 'goldenrod-city', 'area': 'train-station'},
            0x08: {'location': 'goldenrod-city', 'area': 'flower-shop'},
            0x09: {'location': 'goldenrod-city', 'area': 'pp-speech-house'},
            0x0A: {'location': 'goldenrod-city', 'area': 'name-rater'},
            0x0B: {'location': 'goldenrod-city', 'area': 'department-store/1f'},
            0x0C: {'location': 'goldenrod-city', 'area': 'department-store/2f'},
            0x0D: {'location': 'goldenrod-city', 'area': 'department-store/3f'},
            0x0E: {'location': 'goldenrod-city', 'area': 'department-store/4f'},
            0x0F: {'location': 'goldenrod-city', 'area': 'department-store/5f'},
            0x10: {'location': 'goldenrod-city', 'area': 'department-store/6f'},
            0x11: {'location': 'goldenrod-city', 'area': 'department-store/elevator'},
            0x12: {'location': 'goldenrod-city', 'area': 'department-store/roof'},
            0x13: {'location': 'goldenrod-city', 'area': 'game-corner'},
            0x14: {'location': 'goldenrod-city', 'area': 'pokemon-center'},
            0x15: {'location': 'pokecom-center-admin-office-mobile', 'area': 'whole-area'},
            0x16: {'location': 'ilex-forest', 'area': 'azalea-gate'},
            0x17: {'location': 'johto-route-34', 'area': 'ilex-forest-gate'},
            0x18: {'location': 'johto-route-34', 'area': 'day-care'},
        }
    map_slugs[0x0C] = {
        0x01: {'location': 'kanto-route-6', 'area': 'whole-area'},
        0x02: {'location': 'kanto-route-11', 'area': 'whole-area'},
        0x03: {'location': 'vermilion-city', 'area': 'whole-area'},
        0x04: {'location': 'vermilion-city', 'area': 'fishing-guru'},
        0x05: {'location': 'vermilion-city', 'area': 'pokemon-center'},
        0x06: {'location': 'vermilion-pokecenter-2f-beta', 'area': 'whole-area'},
        0x07: {'location': 'vermilion-city', 'area': 'pokemon-fan-club'},
        0x08: {'location': 'vermilion-city', 'area': 'magnet-train-speech-house'},
        0x09: {'location': 'vermilion-city', 'area': 'mart'},
        0x0A: {'location': 'vermilion-city', 'area': 'digletts-cave-speech-house'},
        0x0B: {'location': 'vermilion-city', 'area': 'gym'},
        0x0C: {'location': 'kanto-route-6', 'area': 'saffron-gate'},
        0x0D: {'location': 'kanto-underground-path-5-6', 'area': 'route-6'},
    }
    map_slugs[0x0D] = {
        0x01: {'location': 'kanto-route-1', 'area': 'whole-area'},
        0x02: {'location': 'pallet-town', 'area': 'whole-area'},
        0x03: {'location': 'pallet-town', 'area': 'reds-house-1f'},
        0x04: {'location': 'pallet-town', 'area': 'reds-house-2f'},
        0x05: {'location': 'pallet-town', 'area': 'blues-house'},
        0x06: {'location': 'pallet-town', 'area': 'oaks-lab'},
    }
    map_slugs[0x0E] = {
        0x01: {'location': 'kanto-route-3', 'area': 'whole-area'},
        0x02: {'location': 'pewter-city', 'area': 'whole-area'},
        0x03: {'location': 'pewter-city', 'area': 'nidoran-speech-house'},
        0x04: {'location': 'pewter-city', 'area': 'gym'},
        0x05: {'location': 'pewter-city', 'area': 'mart'},
        0x06: {'location': 'pewter-city', 'area': 'pokemon-center'},
        0x07: {'location': 'pewter-pokecenter-2f-beta', 'area': 'whole-area'},
        0x08: {'location': 'pewter-city', 'area': 'snooze-speech-house'},
    }
    map_slugs[0x0F] = {
        0x01: {'location': 'olivine-city', 'area': 'dock'},
        0x02: {'location': 'vermilion-city', 'area': 'dock'},
        0x03: {'location': 'ss-aqua', 'area': '1f'},
        0x04: {'location': 'ss-aqua', 'area': ['cabin-1', 'cabin-4', 'cabin-3']},
        0x05: {'location': 'ss-aqua', 'area': ['cabin-6', 'cabin-5', 'cabin-2']},
        0x06: {'location': 'ss-aqua', 'area': ['cabin-7', 'cabin-8', 'captains-cabin']},
        0x07: {'location': 'ss-aqua', 'area': 'b1f'},
        0x08: {'location': 'olivine-city', 'area': 'port-passage'},
        0x09: {'location': 'vermilion-city', 'area': 'port-passage'},
        0x0A: {'location': 'mt-moon', 'area': 'mt-moon-square'},
        0x0B: {'location': 'mt-moon', 'area': 'mt-moon-square/gift-shop'},
        0x0C: {'location': 'tin-tower', 'area': 'roof'},
    }
    map_slugs[0x10] = {
        0x01: {'location': 'kanto-route-23', 'area': 'whole-area'},
        0x02: {'location': 'indigo-plateau', 'area': 'lobby'},
        0x03: {'location': 'indigo-plateau', 'area': 'wills-room'},
        0x04: {'location': 'indigo-plateau', 'area': 'kogas-room'},
        0x05: {'location': 'indigo-plateau', 'area': 'brunos-room'},
        0x06: {'location': 'indigo-plateau', 'area': 'karens-room'},
        0x07: {'location': 'indigo-plateau', 'area': 'lances-room'},
        0x08: {'location': 'indigo-plateau', 'area': 'hall-of-fame'},
    }
    map_slugs[0x11] = {
        0x01: {'location': 'kanto-route-13', 'area': 'whole-area'},
        0x02: {'location': 'kanto-route-14', 'area': 'whole-area'},
        0x03: {'location': 'kanto-route-15', 'area': 'whole-area'},
        0x04: {'location': 'kanto-route-18', 'area': 'whole-area'},
        0x05: {'location': 'fuchsia-city', 'area': 'whole-area'},
        0x06: {'location': 'fuchsia-city', 'area': 'mart'},
        0x07: {'location': 'fuchsia-city', 'area': 'safari-zone-staff'},
        0x08: {'location': 'fuchsia-city', 'area': 'gym'},
        0x09: {'location': 'fuchsia-city', 'area': 'bills-brothers-house'},
        0x0A: {'location': 'fuchsia-city', 'area': 'pokemon-center'},
        0x0B: {'location': 'fuchsia-pokecenter-2f-beta', 'area': 'whole-area'},
        0x0C: {'location': 'fuchsia-city', 'area': 'wardens-house'},
        0x0D: {'location': 'kanto-route-15', 'area': 'fuchsia-gate'},
    }
    map_slugs[0x12] = {
        0x01: {'location': 'kanto-route-8', 'area': 'whole-area'},
        0x02: {'location': 'kanto-route-12', 'area': 'whole-area'},
        0x03: {'location': 'kanto-route-10', 'area': 'lavender-town'},
        0x04: {'location': 'lavender-town', 'area': 'whole-area'},
        0x05: {'location': 'lavender-town', 'area': 'pokemon-center'},
        0x06: {'location': 'lavender-pokecenter-2f-beta', 'area': 'whole-area'},
        0x07: {'location': 'lavender-town', 'area': 'mr-fujis-house'},
        0x08: {'location': 'lavender-town', 'area': 'speech-house'},
        0x09: {'location': 'lavender-town', 'area': 'name-rater'},
        0x0A: {'location': 'lavender-town', 'area': 'mart'},
        0x0B: {'location': 'lavender-town', 'area': 'soul-house'},
        0x0C: {'location': 'lavender-radio-tower', 'area': '1f'},
        0x0D: {'location': 'kanto-route-8', 'area': 'saffron-gate'},
        0x0E: {'location': 'kanto-route-12', 'area': 'fishing-guru'},
    }
    map_slugs[0x13] = {
        0x01: {'location': 'kanto-route-28', 'area': 'whole-area'},
        0x02: {'location': 'silver-cave', 'area': 'outside'},
        0x03: {'location': 'kanto-route-28', 'area': 'pokemon-center'},
        0x04: {'location': 'kanto-route-28', 'area': 'steel-wing-house'},
    }
    # These are all of the special maps, e.g. trading room
    map_slugs[0x14] = {
        0x01: {'location': 'pokecenter-2f', 'area': 'whole-area'},
        0x02: {'location': 'trade-center', 'area': 'whole-area'},
        0x03: {'location': 'colosseum', 'area': 'whole-area'},
        0x04: {'location': 'time-capsule', 'area': 'whole-area'},
        # Maps 0x05 - 0x06 are only in Crystal
        0x05: {'location': 'mobile-trade-room', 'area': 'whole-area'},
        0x06: {'location': 'mobile-battle-room', 'area': 'whole-area'},
    }
    if version_group == 'gold-silver':
        del map_slugs[0x14][0x05]
        del map_slugs[0x14][0x06]
        map_slugs[0x14] = _reorder_maps(map_slugs[0x14])
    map_slugs[0x15] = {
        0x01: {'location': 'kanto-route-7', 'area': 'whole-area'},
        0x02: {'location': 'kanto-route-16', 'area': 'whole-area'},
        0x03: {'location': 'kanto-route-17', 'area': 'whole-area'},
        0x04: {'location': 'celadon-city', 'area': 'whole-area'},
        0x05: {'location': 'celadon-city', 'area': 'department-store/1f'},
        0x06: {'location': 'celadon-city', 'area': 'department-store/2f'},
        0x07: {'location': 'celadon-city', 'area': 'department-store/3f'},
        0x08: {'location': 'celadon-city', 'area': 'department-store/4f'},
        0x09: {'location': 'celadon-city', 'area': 'department-store/5f'},
        0x0A: {'location': 'celadon-city', 'area': 'department-store/6f'},
        0x0B: {'location': 'celadon-city', 'area': 'department-store/elevator'},
        0x0C: {'location': 'celadon-city', 'area': 'celadon-mansion/1f'},
        0x0D: {'location': 'celadon-city', 'area': 'celadon-mansion/2f'},
        0x0E: {'location': 'celadon-city', 'area': 'celadon-mansion/3f'},
        0x0F: {'location': 'celadon-city', 'area': 'celadon-mansion/roof'},
        0x10: {'location': 'celadon-city', 'area': 'celadon-mansion/roof-house'},
        0x11: {'location': 'celadon-city', 'area': 'pokemon-center'},
        0x12: {'location': 'celadon-pokecenter-2f-beta', 'area': 'whole-area'},
        0x13: {'location': 'celadon-city', 'area': 'game-corner'},
        0x14: {'location': 'celadon-city', 'area': 'game-corner-prize-room'},
        0x15: {'location': 'celadon-city', 'area': 'gym'},
        0x16: {'location': 'celadon-city', 'area': 'diner'},
        0x17: {'location': 'kanto-route-16', 'area': 'fuchsia-speech-house'},
        0x18: {'location': 'kanto-route-16', 'area': 'gate'},
        0x19: {'location': 'kanto-route-7', 'area': 'saffron-gate'},
        0x1A: {'location': 'kanto-route-17', 'area': 'route-18-gate'},
    }
    map_slugs[0x16] = {
        0x01: {'location': 'johto-route-40', 'area': 'whole-area'},
        0x02: {'location': 'johto-route-41', 'area': 'whole-area'},
        0x03: {'location': 'cianwood-city', 'area': 'whole-area'},
        0x04: {'location': 'cianwood-city', 'area': 'manias-house'},
        0x05: {'location': 'cianwood-city', 'area': 'gym'},
        0x06: {'location': 'cianwood-city', 'area': 'pokemon-center'},
        0x07: {'location': 'cianwood-city', 'area': 'pharmacy'},
        0x08: {'location': 'cianwood-city', 'area': 'photo-studio'},
        0x09: {'location': 'cianwood-city', 'area': 'lugia-speech-house'},
        # Maps 0x0A - 0x10 are only in Crystal
        0x0A: {'location': 'poke-seers-house', 'area': 'poke-seers-house'},
        0x0B: {'location': 'johto-battle-tower', 'area': 'lobby'},
        0x0C: {'location': 'johto-battle-tower', 'area': 'battle-room'},
        0x0D: {'location': 'johto-battle-tower', 'area': 'elevator'},
        0x0E: {'location': 'johto-battle-tower', 'area': 'hallway'},
        0x0F: {'location': 'johto-route-40', 'area': 'battle-tower-gate'},
        0x10: {'location': 'johto-battle-tower', 'area': 'outside'},
    }
    if version_group == 'gold-silver':
        for map_id in range(0x0A, 0x11):
            del map_slugs[0x16][map_id]
        map_slugs[0x16] = _reorder_maps(map_slugs[0x16])

    map_slugs[0x17] = {
        0x01: {'location': 'kanto-route-2', 'area': 'whole-area'},
        0x02: {'location': 'kanto-route-22', 'area': 'whole-area'},
        0x03: {'location': 'viridian-city', 'area': 'whole-area'},
        0x04: {'location': 'viridian-city', 'area': 'gym'},
        0x05: {'location': 'viridian-city', 'area': 'nickname-speech-house'},
        0x06: {'location': 'viridian-city', 'area': 'trainer-house-1f'},
        0x07: {'location': 'viridian-city', 'area': 'trainer-house-b1f'},
        0x08: {'location': 'viridian-city', 'area': 'mart'},
        0x09: {'location': 'viridian-city', 'area': 'pokemon-center'},
        0x0A: {'location': 'viridian-pokecenter-2f-beta', 'area': 'whole-area'},
        0x0B: {'location': 'kanto-route-2', 'area': 'nugget-house'},
        0x0C: {'location': 'kanto-route-2', 'area': 'gate'},
        0x0D: {'location': 'kanto-victory-road', 'area': 'gate'},
    }
    map_slugs[0x18] = {
        0x01: {'location': 'kanto-route-26', 'area': 'whole-area'},
        0x02: {'location': 'kanto-route-27', 'area': 'whole-area'},
        0x03: {'location': 'johto-route-29', 'area': 'whole-area'},
        0x04: {'location': 'new-bark-town', 'area': 'whole-area'},
        0x05: {'location': 'new-bark-town', 'area': 'elms-lab'},
        0x06: {'location': 'new-bark-town', 'area': 'players-house-1f'},
        0x07: {'location': 'new-bark-town', 'area': 'players-house-2f'},
        0x08: {'location': 'new-bark-town', 'area': 'players-neighbors-house'},
        0x09: {'location': 'new-bark-town', 'area': 'elms-house'},
        0x0A: {'location': 'kanto-route-26', 'area': 'heal-house'},
        0x0B: {'location': 'kanto-route-26', 'area': 'day-of-week-siblings-house'},
        0x0C: {'location': 'kanto-route-27', 'area': 'sandstorm-house'},
        0x0D: {'location': 'kanto-route-29', 'area': 'route-46-gate'},
    }
    map_slugs[0x19] = {
        0x01: {'location': 'kanto-route-5', 'area': 'whole-area'},
        0x02: {'location': 'saffron-city', 'area': 'whole-area'},
        0x03: {'location': 'fighting-dojo', 'area': 'fighting-dojo'},
        0x04: {'location': 'saffron-gym', 'area': 'gym'},
        0x05: {'location': 'saffron-mart', 'area': 'mart'},
        0x06: {'location': 'saffron-pokecenter-1f', 'area': 'pokemon-center'},
        0x07: {'location': 'saffron-pokecenter-2f-beta', 'area': 'whole-area'},
        0x08: {'location': 'mr-psychics-house', 'area': 'mr-psychics-house'},
        0x09: {'location': 'saffron-magnet-train-station', 'area': 'magnet-train-station'},
        0x0A: {'location': 'silph-co', 'area': '1f'},
        0x0B: {'location': 'copycats-house-1f', 'area': 'copycats-house-1f'},
        0x0C: {'location': 'copycats-house-2f', 'area': 'copycats-house-2f'},
        0x0D: {'location': 'kanto-underground-path-5-6', 'area': 'route-5'},
        0x0E: {'location': 'kanto-route-5', 'area': 'saffron-gate'},
        0x0F: {'location': 'kanto-route-5', 'area': 'cleanse-tag-house'},
    }
    map_slugs[0x1A] = {
        0x01: {'location': 'johto-route-30', 'area': 'whole-area'},
        0x02: {'location': 'johto-route-31', 'area': 'whole-area'},
        0x03: {'location': 'cherrygrove-city', 'area': 'whole-area'},
        0x04: {'location': 'cherrygrove-city', 'area': 'mart'},
        0x05: {'location': 'cherrygrove-city', 'area': 'pokemon-center'},
        0x06: {'location': 'cherrygrove-city', 'area': 'gym-speech-house'},
        0x07: {'location': 'cherrygrove-city', 'area': 'guide-gents-house'},
        0x08: {'location': 'cherrygrove-city', 'area': 'evolution-speech-house'},
        0x09: {'location': 'johto-route-30', 'area': 'berry-house'},
        0x0A: {'location': 'johto-route-30', 'area': 'mr-pokemons-house'},
        0x0B: {'location': 'johto-route-31', 'area': 'violet-gate'},
    }

    return map_slugs
