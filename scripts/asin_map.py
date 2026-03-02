#!/usr/bin/env python3
"""
ASIN Mapping for Amazon Product Links
Maps Amazon search terms to direct product ASINs.
Used by replace_search_links.py to convert /s?k= links to /dp/ASIN links.
"""

# Format: "search_term_with_plus_signs": "ASIN"
ASIN_MAP = {
    # === ANGLE GRINDERS ===
    "DeWalt+DWE402+angle+grinder": "B00RVZ7DNO",
    "Makita+9557PBX1+angle+grinder": "B0010DHFTK",
    "Milwaukee+2880-20+angle+grinder": "B09RX4R3TR",
    "Bosch+GWS13-50+angle+grinder": "B01CEA1A20",
    "Metabo+HPT+G12VE+angle+grinder": "B07MY5GJRJ",

    # === BATTERY CHAINSAWS ===
    "EGO+Power+56V+Chainsaw": "B0725K9WQG",
    "DeWalt+20V+Max+Chainsaw": "B073FTGBZY",
    "Greenworks+40V+Chainsaw": "B00DRBBRU6",
    "Milwaukee+M18+Chainsaw": "B07FRRFR47",
    "Makita+LXT+Chainsaw": "B01MUCQTK4",

    # === BATTERY POWERED LAWN MOWERS ===
    "EGO+Power+21+inch+Lawn+Mower": "B08GPZ1XLJ",
    "Greenworks+21+inch+Battery+Mower": "B086PSNGPY",
    "Ryobi+20+inch+Battery+Mower": "B088BW2TPB",
    "DeWalt+20V+MAX+Lawn+Mower": "B09MLL5878",
    "Makita+LXT+Lawn+Mower": "B07PX481W4",
    "Toro+60V+Battery+Mower": "B0CPH8G8DY",
    "Kobalt+40V+Lawn+Mower": "B07DG1NBYB",

    # === CORDLESS HEDGE TRIMMERS ===
    "EGO+Power+Hedge+Trimmer": "B00N0A4S1O",
    "EGO+Power+HT2400+Hedge+Trimmer": "B00N0A4S1O",
    "DeWalt+20V+Hedge+Trimmer": "B01BSURQXO",
    "DeWalt+DCHT820P1+20V+MAX+XR+Hedge+Trimmer": "B01BSURQ3O",
    "Milwaukee+M18+Hedge+Trimmer": "B076KYQ8ZQ",
    "Milwaukee+M18+FUEL+2726-21HD+Hedge+Trimmer": "B06Y2CR6QH",
    "Makita+LXT+Hedge+Trimmer": "B07PFG5745",
    "Greenworks+40V+Hedge+Trimmer": "B00AW72V5S",
    "BLACK+DECKER+LHT2436+40V+MAX+Hedge+Trimmer": "B00602J4MM",

    # === PORTABLE AIR COMPRESSORS ===
    "Makita+MAC2400+air+compressor": "B0001Q2VPK",
    "California+Air+Tools+8010": "B00WM1VPKE",
    "DeWalt+DWFP55126+air+compressor": "B00K34UZBW",
    "Porter+Cable+C2002+air+compressor": "B000O5RO1Y",
    "Viair+88P+portable+compressor": "B005ASY23I",
    "Husky+4.5+gallon+air+compressor": "B07T87CF27",

    # === CHAINSAWS ===
    "Stihl+MS+250+Chainsaw": "NOT_ON_AMAZON",
    "Husqvarna+450+Rancher+Chainsaw": "B0BRNRXWJB",
    "Echo+CS-590+Chainsaw": "B00KXM6OT6",
    "EGO+Power+Chainsaw": "B0725K9WQG",
    "DeWalt+20V+Chainsaw": "B073FTGBZY",

    # === CIRCULAR SAWS ===
    "DeWalt+DWE575+circular+saw": "B007QUZ106",
    "Makita+5007F+circular+saw": "B004YIALZI",
    "Milwaukee+M18+Fuel+circular+saw": "B00PGQ6D2W",
    "Skil+5280-01+circular+saw": "B01BD81BLO",
    "Bosch+CS10+circular+saw": "B0001X21PS",

    # === CORDLESS DRILLS ===
    "DeWalt+20V+MAX+XR+DCD771C2": "B00ET5VMTU",
    "Milwaukee+M18+Fuel+2804-20": "B079NBC7JN",
    "Makita+XPH12Z+18V+Drill": "B01M4HGFRS",
    "Bosch+GSR18V-190B22+Drill": "B076SH7F1Q",
    "Ryobi+P1813+18V+ONE+Drill": "B01NA0658V",

    # === CORDLESS LEAF BLOWERS ===
    "EGO+Power+56V+leaf+blower": "B0BM2Y2DBJ",
    "DeWalt+20V+leaf+blower": "B01C5YWSBW",
    "Milwaukee+M18+leaf+blower": "B078ZQX746",
    "Makita+LXT+leaf+blower": "B07YYFCG5P",
    "Greenworks+40V+leaf+blower": "B00AW72V4O",
    "Ryobi+40V+leaf+blower": "B099MTQSMR",

    # === ELECTRIC PRESSURE WASHERS ===
    "Sun+Joe+SPX3000": "B00CPGMUXW",
    "Greenworks+2000+PSI+pressure+washer": "B0989M4WJ1",
    "Ryobi+2000+PSI+pressure+washer": "B01I47JVWS",
    "Karcher+K5+pressure+washer": "B01BMETE40",

    # === ELECTRIC SNOW BLOWERS ===
    "EGO+Power+snow+blower": "B01MYURMK7",
    "EGO+Power+SNT2102+21-Inch+Snow+Blower": "B01MYURMK7",
    "Greenworks+20+inch+electric+snow+blower": "B00PBYZ6LS",
    "Snow+Joe+SJ627E": "B075NMXLSS",
    "Snow+Joe+SJ627E+22-Inch+Electric+Snow+Blower": "B075NMXLSS",
    "Snow+Joe+SJ618E": "B00W8YAVXM",
    "Snow+Joe+SJ618E+Ultra+18-Inch+Electric": "B00W8YAVXM",
    "Toro+Power+Curve+1800": "B003FIQKYO",
    "Toro+38381+Power+Curve+Electric+Snow+Blower": "B003FIQKYO",
    "Ryobi+40V+snow+blower": "B07KN3FNWB",

    # === IMPACT DRIVERS ===
    "DeWalt+DCF887+impact+driver": "B0183RLW8A",
    "Milwaukee+M18+Fuel+impact+driver": "B08DKK3XK2",
    "Makita+XDT16Z+impact+driver": "B07N9JBDK5",
    "Bosch+GDR18V-1400+impact+driver": "B07SHVK783",
    "Ryobi+P238+impact+driver": "B077GNX1KW",

    # === INVERTER GENERATORS ===
    "Honda+EU2200i": "B079YF1HF6",
    "Yamaha+EF2000iSv2": "B07MY4G5QJ",
    "Champion+100692+inverter": "B0812PNK3X",
    "Westinghouse+iGen2200": "B01MUP6L1U",
    "WEN+56235i": "B085828BQ6",

    # === JIGSAWS ===
    "DeWalt+DCS334B+jigsaw": "B07JPFHQKG",
    "Makita+4329K+jigsaw": "B000XULXEO",
    "Bosch+JS470E+jigsaw": "B004323NPK",
    "Milwaukee+M18+jigsaw": "B07H7DQ3DK",
    "Skil+4495-02+jigsaw": "B00APL6RR4",

    # === LAWN MOWERS ===
    "Honda+HRX+Lawn+Mower": "B00S6Z2GWQ",
    "Toro+Recycler+21+inch+Lawn+Mower": "B0CPFY2455",

    # === LEAF BLOWERS ===
    "Stihl+BR+600+backpack+blower": "B01CIELOQ4",
    "Husqvarna+125B+leaf+blower": "B002P2ZVQ4",
    "Ryobi+18V+ONE+leaf+blower": "B07TNWF823",

    # === MITER SAWS ===
    "DeWalt+DWS780": "B00540JS7C",
    "Makita+LS1019L": "B073K99HWJ",
    "Bosch+GCM12SD": "B004323NNC",
    "DeWalt+DWS715": "B07P8QTFRC",
    "Metabo+HPT+C10FCGS": "B07PX44JQM",

    # === OSCILLATING MULTI-TOOLS ===
    "DeWalt+DCS356+oscillating+tool": "B07VBB55X5",
    "Makita+XMT03Z+oscillating+tool": "B00LIV11RG",
    "Fein+MultiMaster+oscillating+tool": "B01E73VTX2",
    "Bosch+GOP40-30C+oscillating+tool": "B01ER7J976",
    "Rockwell+Sonicrafter+oscillating+tool": "B01HD4EQTG",

    # === PORTABLE GENERATORS ===
    "Westinghouse+WGen7500": "B01N80F68E",
    "DuroMax+XP8500E": "B001BMA09W",
    "Champion+7500+generator": "B01A0TLE5U",
    "Briggs+Stratton+5500+generator": "B085FWK6JT",
    "Honda+EU3000iS": "B076BYPN1B",

    # === PRESSURE WASHERS ===
    "Simpson+MSH3125+pressure+washer": "B004MXKUCY",
    "Honda+GCV+pressure+washer": "B074J6R3K1",

    # === RANDOM ORBITAL SANDERS ===
    "DeWalt+DWE6423+random+orbital+sander": "B0858C7X3M",
    "Makita+BO5041K+random+orbital+sander": "B003M5IWM8",
    "Bosch+ROS20VSC+random+orbital+sander": "B00BD5G9VA",
    "Milwaukee+M18+random+orbital+sander": "B07BM93DK1",
    "Festool+ETS+125+random+orbital+sander": "B084DVBHSB",

    # === RECIPROCATING SAWS ===
    "DeWalt+DWE305+reciprocating+saw": "B00N5FLVM8",
    "Milwaukee+M18+Fuel+reciprocating+saw": "B08WG2HC81",
    "Makita+XRJ05+reciprocating+saw": "B01IU92IYI",
    "Bosch+RS325+reciprocating+saw": "B004323NNM",
    "Ryobi+P517+reciprocating+saw": "B077RQSVYN",

    # === SNOW BLOWERS ===
    "Ariens+Deluxe+24+snow+blower": "B01JJ6UL04",
    "Ariens+Deluxe+28+SHO+921049": "B01JJ6UL04",
    "Husqvarna+ST224+snow+blower": "B09PF7SF45",
    "Toro+Power+Max+824": "B0755P6CR4",
    "Toro+Power+Max+826+OAE+37802": "B0755P6CR4",
    "Cub+Cadet+3X+snow+blower": "B085TC3376",
    "Cub+Cadet+3X+30+MAX+31AH7S7G710": "B085TC3376",

    # === TABLE SAWS ===
    "DeWalt+DWE7491RS+table+saw": "B00F2CGXGG",
    "Bosch+4100XC-10+table+saw": "B0851KL858",
    "SawStop+CTS+table+saw": "B0B2TJFH24",
    "Skil+TS6307-00+table+saw": "B08F9RFJ2K",
    "Ridgid+R4520+table+saw": "B08PRZZZP1",
}
