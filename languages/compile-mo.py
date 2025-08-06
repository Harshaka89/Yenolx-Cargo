#!/usr/bin/env python3
"""
Simple PO to MO compiler
"""

import sys
import struct

def compile_po_to_mo(po_file, mo_file=None):
    if mo_file is None:
        mo_file = po_file.replace('.po', '.mo')
    
    translations = {}
    current_msgid = ''
    current_msgstr = ''
    in_msgid = False
    in_msgstr = False
    
    with open(po_file, 'r', encoding='utf-8') as f:
        for line in f:
            line = line.strip()
            
            if not line or line.startswith('#'):
                continue
            
            if line.startswith('msgid '):
                if in_msgstr:
                    translations[current_msgid] = current_msgstr
                current_msgid = line[7:-1]  # Remove "msgid " and quotes
                current_msgstr = ''
                in_msgid = True
                in_msgstr = False
            elif line.startswith('msgstr '):
                current_msgstr = line[8:-1]  # Remove "msgstr " and quotes
                in_msgid = False
                in_msgstr = True
            elif in_msgid and line.startswith('"'):
                current_msgid += line[1:-1]
            elif in_msgstr and line.startswith('"'):
                current_msgstr += line[1:-1]
    
    if in_msgstr:
        translations[current_msgid] = current_msgstr
    
    # Create MO file
    num_entries = len(translations)
    
    # MO file header
    mo_data = struct.pack('<IIII', 
                          0x950412de,  # Magic number
                          0,           # Version
                          num_entries, # Number of entries
                          28)          # Offset of original strings table
    
    # Prepare string data
    original_strings = b''
    translation_strings = b''
    original_offsets = []
    translation_offsets = []
    
    for original, translation in translations.items():
        original_bytes = original.encode('utf-8')
        translation_bytes = translation.encode('utf-8')
        
        original_offsets.append((len(original_bytes), len(original_strings)))
        original_strings += original_bytes + b'\0'
        
        translation_offsets.append((len(translation_bytes), len(translation_strings)))
        translation_strings += translation_bytes + b'\0'
    
    # Write original strings table
    for length, offset in original_offsets:
        mo_data += struct.pack('<II', length, offset)
    
    # Write translation strings table
    for length, offset in translation_offsets:
        mo_data += struct.pack('<II', length, offset)
    
    # Write string data
    mo_data += original_strings
    mo_data += translation_strings
    
    with open(mo_file, 'wb') as f:
        f.write(mo_data)
    
    print(f"Successfully compiled '{po_file}' to '{mo_file}'")
    print(f"Total translations: {num_entries}")

if __name__ == '__main__':
    if len(sys.argv) < 2:
        print("Usage: python3 compile-mo.py <input.po> [output.mo]")
        sys.exit(1)
    
    po_file = sys.argv[1]
    mo_file = sys.argv[2] if len(sys.argv) > 2 else None
    
    compile_po_to_mo(po_file, mo_file)