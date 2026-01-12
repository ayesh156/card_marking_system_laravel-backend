#!/usr/bin/env python3
"""
Script to remove Spoken category (category_id=1) data from database backup.
Spoken tuition IDs: 2, 4, 8, 9, 35

Tables affected:
- tuitions: Remove rows where category_id = 1 (IDs: 2, 4, 8, 9, 35)
- tuitions_has_grades: Remove rows where tuition_id IN (2, 4, 8, 9, 35)
- students_has_tuitions: Remove rows where tuition_id IN (2, 4, 8, 9, 35)
- student_reports: Remove rows where tuition_id IN (2, 4, 8, 9, 35)
"""

import re
import os

# Define spoken tuition IDs
SPOKEN_TUITION_IDS = {2, 4, 8, 9, 35}

def process_backup_file():
    input_file = r'd:\Learning\laravel-react\laravel-backend\database\zynergy_db_backup.sql'
    output_file = r'd:\Learning\laravel-react\laravel-backend\database\zynergy_db_backup_clean.sql'
    
    with open(input_file, 'r', encoding='utf-8') as f:
        content = f.read()
    
    # Track removal counts
    removed_counts = {
        'tuitions': 0,
        'tuitions_has_grades': 0,
        'students_has_tuitions': 0,
        'student_reports': 0
    }
    
    # Process tuitions table - remove rows where category_id = 1 (second field after id)
    # Pattern: (id, day_id, category_id, class_id, ...)
    def filter_tuitions(match):
        rows = match.group(0)
        lines = rows.split('\n')
        filtered_lines = []
        for line in lines:
            # Check if line contains a tuition row
            row_match = re.search(r'\((\d+),\s*\d+,\s*(\d+),', line)
            if row_match:
                tuition_id = int(row_match.group(1))
                category_id = int(row_match.group(2))
                if category_id == 1:  # Spoken category
                    removed_counts['tuitions'] += 1
                    continue
            filtered_lines.append(line)
        return '\n'.join(filtered_lines)
    
    # Find and process tuitions INSERT block
    tuitions_pattern = r"INSERT INTO `tuitions`[^;]+;"
    match = re.search(tuitions_pattern, content, re.DOTALL)
    if match:
        original = match.group(0)
        # Split into lines and filter
        lines = original.split('\n')
        filtered_lines = []
        in_values = False
        values_lines = []
        
        for line in lines:
            if 'INSERT INTO' in line:
                filtered_lines.append(line)
                in_values = True
                continue
            if in_values:
                # Extract the row data
                row_match = re.search(r'\((\d+),\s*\d+,\s*(\d+),', line)
                if row_match:
                    tuition_id = int(row_match.group(1))
                    category_id = int(row_match.group(2))
                    if category_id == 1:  # Spoken category
                        removed_counts['tuitions'] += 1
                        continue
                values_lines.append(line)
        
        # Fix trailing commas
        if values_lines:
            fixed_values = []
            for i, line in enumerate(values_lines):
                if i == len(values_lines) - 1:
                    # Last line - ensure it ends with ;
                    line = re.sub(r',\s*$', ';', line)
                else:
                    # Not last line - ensure it ends with comma
                    if not line.strip().endswith(',') and not line.strip().endswith(';'):
                        line = line.rstrip() + ','
                fixed_values.append(line)
            filtered_lines.extend(fixed_values)
        
        new_insert = '\n'.join(filtered_lines)
        content = content.replace(original, new_insert)
    
    # Process tuitions_has_grades - remove rows where tuition_id IN SPOKEN_TUITION_IDS
    tuitions_grades_pattern = r"INSERT INTO `tuitions_has_grades`[^;]+;"
    match = re.search(tuitions_grades_pattern, content, re.DOTALL)
    if match:
        original = match.group(0)
        lines = original.split('\n')
        filtered_lines = []
        
        for line in lines:
            if 'INSERT INTO' in line:
                filtered_lines.append(line)
                continue
            # Pattern: (id, tuition_id, grade_id, ...)
            row_match = re.search(r'\(\d+,\s*(\d+),', line)
            if row_match:
                tuition_id = int(row_match.group(1))
                if tuition_id in SPOKEN_TUITION_IDS:
                    removed_counts['tuitions_has_grades'] += 1
                    continue
            filtered_lines.append(line)
        
        # Fix trailing commas
        for i in range(len(filtered_lines) - 1, -1, -1):
            line = filtered_lines[i].strip()
            if line.endswith(','):
                filtered_lines[i] = filtered_lines[i].rstrip()[:-1] + ';'
                break
        
        new_insert = '\n'.join(filtered_lines)
        content = content.replace(original, new_insert)
    
    # Process students_has_tuitions - remove rows where tuition_id IN SPOKEN_TUITION_IDS
    students_tuitions_pattern = r"INSERT INTO `students_has_tuitions`[^;]+;"
    match = re.search(students_tuitions_pattern, content, re.DOTALL)
    if match:
        original = match.group(0)
        lines = original.split('\n')
        filtered_lines = []
        
        for line in lines:
            if 'INSERT INTO' in line:
                filtered_lines.append(line)
                continue
            # Pattern: (id, student_id, tuition_id, ...)
            row_match = re.search(r'\(\d+,\s*\d+,\s*(\d+),', line)
            if row_match:
                tuition_id = int(row_match.group(1))
                if tuition_id in SPOKEN_TUITION_IDS:
                    removed_counts['students_has_tuitions'] += 1
                    continue
            filtered_lines.append(line)
        
        # Fix trailing commas
        for i in range(len(filtered_lines) - 1, -1, -1):
            line = filtered_lines[i].strip()
            if line.endswith(','):
                filtered_lines[i] = filtered_lines[i].rstrip()[:-1] + ';'
                break
        
        new_insert = '\n'.join(filtered_lines)
        content = content.replace(original, new_insert)
    
    # Process student_reports - remove rows where tuition_id IN SPOKEN_TUITION_IDS
    student_reports_pattern = r"INSERT INTO `student_reports`[^;]+;"
    matches = list(re.finditer(student_reports_pattern, content, re.DOTALL))
    
    for match in matches:
        original = match.group(0)
        lines = original.split('\n')
        filtered_lines = []
        
        for line in lines:
            if 'INSERT INTO' in line:
                filtered_lines.append(line)
                continue
            # Pattern: (id, student_id, tuition_id, ...)
            row_match = re.search(r'\(\d+,\s*\d+,\s*(\d+),', line)
            if row_match:
                tuition_id = int(row_match.group(1))
                if tuition_id in SPOKEN_TUITION_IDS:
                    removed_counts['student_reports'] += 1
                    continue
            filtered_lines.append(line)
        
        # Fix trailing commas
        for i in range(len(filtered_lines) - 1, -1, -1):
            line = filtered_lines[i].strip()
            if line.endswith(','):
                filtered_lines[i] = filtered_lines[i].rstrip()[:-1] + ';'
                break
        
        new_insert = '\n'.join(filtered_lines)
        content = content.replace(original, new_insert)
    
    # Write the clean backup
    with open(output_file, 'w', encoding='utf-8') as f:
        f.write(content)
    
    print("=" * 60)
    print("SPOKEN DATA REMOVAL COMPLETE")
    print("=" * 60)
    print(f"\nRemoved rows:")
    print(f"  - tuitions: {removed_counts['tuitions']} rows")
    print(f"  - tuitions_has_grades: {removed_counts['tuitions_has_grades']} rows")
    print(f"  - students_has_tuitions: {removed_counts['students_has_tuitions']} rows")
    print(f"  - student_reports: {removed_counts['student_reports']} rows")
    print(f"\nTotal removed: {sum(removed_counts.values())} rows")
    print(f"\nClean backup saved to: {output_file}")
    print("=" * 60)

if __name__ == '__main__':
    process_backup_file()
