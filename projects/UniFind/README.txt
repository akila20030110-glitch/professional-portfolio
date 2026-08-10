UNIFIND - COMPLETE PROJECT

1. Copy the "UniFind_Complete" folder to:
   C:\xampp\htdocs\

2. You can rename the folder to:
   UniFind

3. Start Apache and MySQL in XAMPP.

4. Open phpMyAdmin:
   http://localhost/phpmyadmin

5. Click SQL and run the contents of:
   install.sql

   The SQL uses CREATE TABLE IF NOT EXISTS, so it will not delete your existing data.

6. Check config/database.php.
   Default XAMPP settings are already:
   host = localhost
   username = root
   password = empty
   database = unifind_db

7. Open:
   http://localhost/UniFind/

8. Create student accounts from Register.

9. Create the first admin account by opening:
   http://localhost/UniFind/create-admin.php

   The admin setup page automatically disables itself after an admin exists.

10. Main student features:
    - Register / Login
    - Report Lost Item
    - Report Found Item
    - Search Items
    - Possible Match System
    - My Reports
    - Edit / Delete Reports
    - Claim Found Item
    - My Claims
    - Notifications

11. Admin features:
    - Admin Dashboard
    - Approve / Reject Claims
    - Item becomes Returned after approval
    - Notifications sent to student
    - Manage Items
    - Manage Users

IMPORTANT:
Back up your current UniFind folder before replacing it.


UI POLISH VERSION
- Modern glass-style dark UI
- Improved responsive layout
- Cleaner buttons, cards, forms and tables
- No database or PHP logic changes required


INTERACTIVE UI UPDATE
- Stronger cyan hover glow on boxes/cards
- Mouse-follow radial highlight
- Subtle 3D tilt based on cursor movement
- Brighter buttons and icon hover effects
- Image zoom/saturation on item cards
- Effects automatically disabled on touch devices and reduced-motion mode
