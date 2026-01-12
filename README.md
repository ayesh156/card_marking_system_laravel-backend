<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## Before Project add server 
Database
NPM install
composer install

---

## 🧪 System Test API (January 2026)

### Overview

A simple API endpoint to verify the backend is running correctly on the server. Displays a creative animated UI showing system status.

### API Endpoint

| URL | Method | Description |
|-----|--------|-------------|
| `/api/test` | GET | Shows animated "API Working" page with system info |

### What It Shows

- ✓ **Animated checkmark** with bounce & pulse effects
- 🚀 **"ONLINE & RUNNING"** glowing status badge
- **Server Information:**
  - ⏰ Server Time
  - 🐘 PHP Version
  - 🔧 Laravel Version
  - 🌍 Timezone

### Usage

Simply visit:
```
https://your-domain.com/api/test
```

### Controller Location

`app/Http/Controllers/SystemTestController.php`

---

## 🧹 Group Class Data Cleanup (January 2026)

### Overview

All student enrollments and reports for Group classes (Category ID 4) have been removed from the database. The Group tuitions still exist but have no students enrolled.

### What Was Removed

| Data Type | Count Removed |
|-----------|---------------|
| Student Reports | 452 |
| Student Enrollments | 50 |

### Affected Tuition IDs

`3, 5, 36, 37, 38, 39, 40, 41, 42, 43, 44`

### SQL Commands Used

```sql
-- Delete reports
DELETE FROM student_reports WHERE tuition_id IN (3,5,36,37,38,39,40,41,42,43,44);

-- Delete enrollments
DELETE FROM students_has_tuitions WHERE tuition_id IN (3,5,36,37,38,39,40,41,42,43,44);
```

### Verification Query

```sql
-- Should return 0 for both
SELECT 
  (SELECT COUNT(*) FROM students_has_tuitions WHERE tuition_id IN (3,5,36,37,38,39,40,41,42,43,44)) as enrollments,
  (SELECT COUNT(*) FROM student_reports WHERE tuition_id IN (3,5,36,37,38,39,40,41,42,43,44)) as reports;
```

---

## 🔧 Student Form Duplicate Fix (January 2026)

### Overview

Fixed an issue where selecting an existing student from the autocomplete would create a duplicate record instead of updating the existing student.

### Problem

When a user searched for an existing student and selected them from the autocomplete dropdown, clicking "Add" would create a new student record instead of recognizing it as an existing student.

### Solution

Updated `StudentPage.jsx` to check if the form has an `id` value before deciding whether to create or update:

```javascript
// Before (buggy)
if (existingStudent) {
    // update
} else {
    // create
}

// After (fixed)
if (values.id) {
    // update existing student
} else {
    // create new student
}
```

### File Changed

`react-front/src/views/StudentPage.jsx` - `handleFormSubmit` function

---

## 📅 Archived Grade Marking Deadline Feature (January 2026)

### Overview

Archived grades like "Grade 11 2025" can now be marked (attendance and payment) until a specific deadline date. This allows teachers to complete marking for students who have graduated or moved to the next academic year.

### How It Works

| Grade | Marking Deadline | Can Mark Until |
|-------|-----------------|----------------|
| Grade 11 2025 | 2026-02-28 | End of February 2026 |
| Grade 11 2026 | 2027-02-28 | End of February 2027 |
| Regular Grades | No deadline | Always |

### Database Migration

```bash
php artisan migrate
```

**Migration file:** `2026_01_12_000000_add_marking_deadline_to_grades_table.php`

This migration:
- Adds `marking_deadline` column to `grades` table
- Sets Grade 11 2025 deadline to 2026-02-28

### API Response Changes

The `fetchStudentData` endpoint now returns two additional fields:

```json
{
  "tuitionId": 19,
  "students": [...],
  "dataYear": 2025,
  "isHistorical": true,
  "canBeMarked": true,
  "markingDeadline": "2026-02-28"
}
```

| Field | Description |
|-------|-------------|
| `canBeMarked` | `true` if marking is still allowed, `false` if deadline passed |
| `markingDeadline` | The deadline date (YYYY-MM-DD) or `null` for regular grades |

### Automatic Deadline Setting

When running grade promotions, archived grades automatically receive a marking deadline:

```bash
php artisan grades:promote --year=2027
```

This will set `Grade 11 2026` marking deadline to `2027-02-28`.

### Manual Deadline Update

To manually set or update a marking deadline:

```sql
-- Set deadline for Grade 11 2025
UPDATE grades SET marking_deadline = '2026-02-28' WHERE grade_name = 'Grade 11 2025';

-- Remove deadline (allow indefinite marking)
UPDATE grades SET marking_deadline = NULL WHERE grade_name = 'Grade 11 2025';
```

---

## 🎓 English Group Classes (Grade 2 - Grade 10)

### Overview

English Group classes have been added for grades 2 through 10. These are Saturday classes under the "Group" category.

### Adding English Group Classes

Run the SQL script to add tuitions:

```bash
# From MySQL client or HeidiSQL
SOURCE database/sql/add_english_group_classes_grade2_to_grade10.sql;
```

### Class Schedule

| Day | Category | Class | Grades |
|-----|----------|-------|--------|
| Saturday | Group | English | Grade 2, 3, 4, 5, 6, 7, 8, 9, 10 |

### SQL Script Location

`database/sql/add_english_group_classes_grade2_to_grade10.sql`

### Verification Query

```sql
-- Check all English Group tuitions
SELECT t.id as tuition_id, d.day_name, c.category_name, cl.class_name, g.grade_name
FROM tuitions t
JOIN days d ON t.day_id = d.id
JOIN categories c ON t.category_id = c.id
JOIN classes cl ON t.class_id = cl.id
JOIN tuitions_has_grades thg ON t.id = thg.tuition_id
JOIN grades g ON thg.grade_id = g.id
WHERE t.category_id = 4 AND t.class_id = 1
ORDER BY g.id;
```

---

## 🔄 Student Identification System Update (January 2026)

### Overview

The student identification system has been simplified by removing the custom `sno` (Student Number) field. The system now uses the standard database `id` (auto-increment primary key) as the unique identifier for all students.

### Database Migration

A migration was created to remove the `sno` column:

```bash
# Run the migration
php artisan migrate
```

**Migration file:** `2026_01_05_150746_remove_sno_from_students_table.php`

### Backend Changes

| File | Change |
|------|--------|
| `app/Models/Student.php` | Removed `sno` from `$fillable` array |
| `app/Http/Requests/StudentRequest.php` | Removed `sno` validation rules |
| `app/Http/Controllers/StudentController.php` | Changed `updateStatus($sno)` to `updateStatus($id)` |
| `routes/api.php` | Changed route from `/student/status/{sno}` to `/student/status/{id}` |
| `app/Http/Controllers/StudentReportController.php` | Removed `sno` from API responses |

### API Changes

| Endpoint | Before | After |
|----------|--------|-------|
| Update Status | `PUT /api/student/status/{sno}` | `PUT /api/student/status/{id}` |

### Impact on Grade Promotion

✅ **No impact on grade promotion system!**

The grade promotion commands (`grades:promote` and `grades:merge`) were already designed to use `student_id` (primary key) for all operations. They never relied on the `sno` field.

### Data Preservation

- Existing student data is fully preserved
- The `id` column (primary key) remains unchanged
- All relationships (`students_has_tuitions`, `student_reports`) continue to work
- Historical data in archive tables is unaffected

---

## 📚 Annual Grade Promotion System

### Overview

This system includes an automated grade promotion command that should be run at the **beginning of each new academic year**. It promotes all students to the next grade level while preserving historical data for reference.

### How It Works

The `grades:promote` command performs the following operations:

| Current Grade | New Grade | Action |
|--------------|-----------|--------|
| Grade 11 | Grade 11 {previousYear} | **Archived** - Graduating class preserved with year label |
| Grade 10 | Grade 11 {newYear} | **Promoted** - New senior class |
| Grade 9 → Grade 2 | +1 level | **Promoted** - Move up one grade |
| **Grade 1a + Grade 1b** | **Grade 2** | **🔀 MERGED** - Both groups combined into single Grade 2 |
| Grade 1a/1b {newYear} | Grade 1a/1b | **Renamed** - Incoming students (if pre-created) |
| Nursery | [ARCHIVED & DELETED] | **Archived** - Data preserved, then deleted |
| [NEW] | Nursery | **Created** - Fresh Nursery class for new academic year |

### Grade 1a/1b → Grade 2 Merge Feature

**Key Feature:** When students complete Grade 1 (which is split into Group A and Group B), they are automatically **merged into a single unified Grade 2 class**.

**How the merge works:**
1. **Grade 1a becomes Grade 2** (renamed, keeps its tuitions)
2. **Grade 1b students are migrated** to Grade 2 tuitions
3. **Grade 1b is archived** (historical data preserved) then deleted
4. **Student enrollments are intelligently mapped** - Grade 1b tuitions are matched to Grade 1a tuitions by day/category/class
5. **Student reports are preserved** and linked to the new tuitions

### Data Protection

All changes are fully audited and archived:
- **`grade_promotion_history`** - Complete audit trail of all grade changes (including new Nursery creation)
- **`archived_grades`** - Preserves deleted grades (e.g., previous year's Nursery)
- **`archived_student_tuitions`** - Preserves deleted student enrollments
- **`academic_year_id`** - Tags enrollments with their academic year for historical queries

### Usage Instructions

#### Step 1: Preview Changes (Dry Run)
Always run a dry-run first to see what will happen:
```bash
php artisan grades:promote --dry-run --year=2027
```

#### Step 2: Execute Promotion
Once satisfied with the preview, run the actual promotion:
```bash
php artisan grades:promote --year=2027 --performed-by="Admin Name"
```

#### Command Options
| Option | Description | Default |
|--------|-------------|---------|
| `--dry-run` | Preview changes without applying them | false |
| `--year=YYYY` | Target academic year for promotion | 2026 |
| `--performed-by=NAME` | Name/email of person performing promotion | system |

### Annual Checklist (Run Every January)

1. **Backup your database** before running the promotion
2. **Run dry-run** to preview changes:
   ```bash
   php artisan grades:promote --dry-run --year={NEW_YEAR}
   ```
3. **Review the promotion plan** carefully
4. **Execute the promotion**:
   ```bash
   php artisan grades:promote --year={NEW_YEAR} --performed-by="Your Name"
   ```
5. **Verify** the grades were updated correctly in the database
6. **Fresh Nursery class is automatically created** - No manual creation needed!
7. **(Optional)** Pre-create next year's incoming Grade 1 classes:
   - Add "Grade 1a {NEXT_YEAR}" and "Grade 1b {NEXT_YEAR}" for new students

### Querying Historical Data

After promotion, you can query historical data using the batch ID provided:

```sql
-- View all promotions in a batch
SELECT * FROM grade_promotion_history WHERE batch_id = 'YOUR_BATCH_ID';

-- View archived Nursery/deleted enrollments
SELECT * FROM archived_student_tuitions WHERE batch_id = 'YOUR_BATCH_ID';

-- Query students by academic year
SELECT s.*, sht.academic_year_id, y.year 
FROM students s
JOIN students_has_tuitions sht ON s.id = sht.student_id
JOIN years y ON sht.academic_year_id = y.id
WHERE y.year = 2025;

-- View all archived grades (including merged Grade 1b)
SELECT * FROM archived_grades ORDER BY academic_year_id;

-- Find all merged student enrollments from Grade 1b
SELECT ast.*, s.name as student_name, gph.notes
FROM archived_student_tuitions ast
JOIN students s ON ast.student_id = s.id
JOIN grade_promotion_history gph ON ast.batch_id = gph.batch_id
WHERE gph.action = 'merged';

-- Track a student's promotion history across years
SELECT s.name, gph.old_grade_name, gph.new_grade_name, gph.action, y.year as promotion_year
FROM students s
JOIN students_has_tuitions sht ON s.id = sht.student_id
JOIN tuitions_has_grades thg ON sht.tuition_id = thg.tuition_id
JOIN grade_promotion_history gph ON thg.grade_id = gph.grade_id
JOIN years y ON gph.to_year_id = y.id
WHERE s.id = YOUR_STUDENT_ID
ORDER BY y.year;
```

### Example: Year-by-Year Progression

**Initial State (2025):**
```
Nursery, Grade 1a, Grade 1b, Grade 2, Grade 3...Grade 11
```

**After 2026 Promotion:**
```
Nursery (fresh - created new)
Grade 1a, Grade 1b (from Grade 1a/1b 2026 - incoming students)
Grade 2 (🔀 MERGED from Grade 1a + Grade 1b - single unified class!)
Grade 3 (from Grade 2)
Grade 4 (from Grade 3)
...
Grade 10 (from Grade 9)
Grade 11 2026 (from Grade 10 - current seniors)
Grade 11 2025 (from Grade 11 - archived graduating class)
```

**After 2027 Promotion:**
```
Nursery (fresh - created new)
Grade 1a, Grade 1b (from Grade 1a/1b 2027 - incoming)
Grade 2 (🔀 MERGED from Grade 1a + Grade 1b)
Grade 3 (from Grade 2)
...
Grade 10 (from Grade 9)
Grade 11 2027 (from Grade 10 - current seniors)
Grade 11 2026 (preserved - graduated class)
Grade 11 2025 (preserved - graduated class)
```

### Data Flow Visualization

```
Year N:                          Year N+1:
┌─────────────┐                  
│   Nursery   │ ──────────────── [ARCHIVED & DELETED]
└─────────────┘                  
                                 ┌─────────────┐
                                 │   Nursery   │ (NEW - fresh class)
                                 └─────────────┘
┌─────────────┐                  
│  Grade 1a   │ ─────┐           
└─────────────┘      │ MERGE     ┌─────────────┐
                     ├─────────► │   Grade 2   │ (unified class)
┌─────────────┐      │           └─────────────┘
│  Grade 1b   │ ─────┘           
└─────────────┘                  

┌─────────────┐                  ┌─────────────┐
│   Grade 2   │ ───────────────► │   Grade 3   │
└─────────────┘                  └─────────────┘
       ...                              ...
┌─────────────┐                  ┌─────────────┐
│  Grade 10   │ ───────────────► │ Grade 11 N+1│ (seniors)
└─────────────┘                  └─────────────┘

┌─────────────┐                  ┌─────────────┐
│  Grade 11   │ ───────────────► │ Grade 11 N  │ (archived)
└─────────────┘                  └─────────────┘
```

### Important Notes

⚠️ **Always backup your database before running promotions!**

⚠️ **Run `--dry-run` first to preview changes!**

⚠️ **Ensure the target year exists in the `years` table** (the command will create it if missing)

⚠️ **Pre-create incoming Grade 1 classes** with year suffix (e.g., "Grade 1a 2027") before promotion if you have new students to add

⚠️ **Grade 1a/1b merge is automatic** - When both Grade 1a and Grade 1b exist, they will be merged into a single Grade 2

### Understanding the Merge Process

When the promotion runs and finds both `Grade 1a` and `Grade 1b`:

1. **Student Migration**: All Grade 1b students are enrolled in Grade 2 tuitions
2. **Tuition Matching**: Grade 1b tuitions are matched to Grade 1a tuitions by:
   - Same day + category + class (best match)
   - Same day + category (good match)
   - Same day (fallback match)
   - Primary tuition (last resort)
3. **Data Preservation**: Original Grade 1b enrollments are archived
4. **Report Migration**: Student reports are moved to the new tuition IDs
5. **Cleanup**: Grade 1b tuitions and grade record are deleted

### Manual Grade Merge Command

If you need to manually merge grades (e.g., if a promotion ran before the merge feature was added):

```bash
# Preview merge
php artisan grades:merge "Grade 2a" "Grade 2b" --dry-run

# Execute merge
php artisan grades:merge "Grade 2a" "Grade 2b" --performed-by="Admin Name"

# Custom target name
php artisan grades:merge "Grade 2a" "Grade 2b" --target="Grade 2" --performed-by="Admin"
```

#### Merge Command Options
| Option | Description | Default |
|--------|-------------|---------|
| `gradeA` | Primary grade (will be renamed) | Required |
| `gradeB` | Grade to merge into primary | Required |
| `--target=NAME` | Target grade name | Removes suffix from gradeA |
| `--dry-run` | Preview without changes | false |
| `--performed-by=NAME` | Who performed merge | system |

---

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

You may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com/)**
- **[Tighten Co.](https://tighten.co)**
- **[WebReinvent](https://webreinvent.com/)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel/)**
- **[Cyber-Duck](https://cyber-duck.co.uk)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Jump24](https://jump24.co.uk)**
- **[Redberry](https://redberry.international/laravel/)**
- **[Active Logic](https://activelogic.com)**
- **[byte5](https://byte5.de)**
- **[OP.GG](https://op.gg)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
