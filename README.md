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

## 📚 Annual Grade Promotion System

### Overview

This system includes an automated grade promotion command that should be run at the **beginning of each new academic year**. It promotes all students to the next grade level while preserving historical data for reference.

### How It Works

The `grades:promote` command performs the following operations:

| Current Grade | New Grade | Action |
|--------------|-----------|--------|
| Grade 11 | Grade 11 {previousYear} | **Archived** - Graduating class preserved with year label |
| Grade 10 | Grade 11 {newYear} | **Promoted** - New senior class |
| Grade 9 → Grade 1 | +1 level | **Promoted** - Move up one grade |
| Grade 1a/1b {newYear} | Grade 1a/1b | **Renamed** - Incoming students (if pre-created) |
| Nursery | [ARCHIVED & DELETED] | **Archived** - Data preserved, then deleted |
| [NEW] | Nursery | **Created** - Fresh Nursery class for new academic year |

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

-- View all archived grades
SELECT * FROM archived_grades ORDER BY academic_year_id;
```

### Example: Year-by-Year Progression

**Initial State (2025):**
```
Nursery, Grade 1a, Grade 1b, Grade 2, Grade 3...Grade 11
```

**After 2026 Promotion:**
```
Grade 1a, Grade 1b (from Grade 1a/1b 2026 - incoming)
Grade 2a, Grade 2b (from Grade 1a, 1b)
Grade 3 (from Grade 2)...Grade 10 (from Grade 9)
Grade 11 2026 (from Grade 10 - current seniors)
Grade 11 2025 (from Grade 11 - archived graduating class)
[Nursery archived and deleted]
```

**After 2027 Promotion:**
```
Grade 1a, Grade 1b (from Grade 1a/1b 2027 - incoming)
Grade 2a, Grade 2b (from Grade 1a, 1b)
Grade 3 (from Grade 2)...Grade 10 (from Grade 9)
Grade 11 2027 (from Grade 10 - current seniors)
Grade 11 2026 (preserved - graduated class)
Grade 11 2025 (preserved - graduated class)
```

### Important Notes

⚠️ **Always backup your database before running promotions!**

⚠️ **Run `--dry-run` first to preview changes!**

⚠️ **Ensure the target year exists in the `years` table** (the command will create it if missing)

⚠️ **Pre-create incoming Grade 1 classes** with year suffix (e.g., "Grade 1a 2027") before promotion if you have new students to add

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
