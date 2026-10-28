SAVELIFE - Pet Adoption and Intake System (HTML, CSS, JavaScript, PHP, MySQL)

SETUP (XAMPP)
1. Copy this "savelife" folder into C:\xampp\htdocs\
2. Start Apache and MySQL in the XAMPP Control Panel.
3. In phpMyAdmin, Import savelife_schema.sql.
   (Optional) then import sample_data.sql to get 4 demo animals.
4. Open http://localhost/savelife/create_admin.php, create the admin account,
   then DELETE create_admin.php.
5. Optional: put a puppy photo at assets/img/puppy.jpg for the login and home banners.
6. Open http://localhost/savelife/register.php to create a user account.

If you rename the folder, change BASE_URL in includes/auth.php.
If MySQL has a root password, set it in includes/db.php.

PAGES
User:  user/dashboard.php  user/animals.php  user/adopt.php  user/my_requests.php
       user/notifications.php  user/report_stray.php
Admin: admin/dashboard.php  admin/animals.php  admin/animal_form.php
       admin/requests.php  admin/strays.php

HOW THE ADOPTION FLOW WORKS
- User picks an animal and sends the adopt form -> request is saved as Pending.
- Admin opens Adoption requests and approves or declines.
- Approve: animal becomes Adopted (hidden from Find A Pet), the user is notified,
  and other pending requests for that animal are declined and notified.
- Decline: the user is notified and the animal stays Available.

SECURITY BUILT IN
Hashed passwords, prepared statements (SQL injection), output escaping (XSS),
CSRF tokens on every form, role checks on every page, image type/size checks,
PHP execution blocked inside the uploads folder.

IDEAS FOR LATER
Edit profile, forgot password, cancel a pending request, pagination,
email notifications, admin user list, "Remember me".
