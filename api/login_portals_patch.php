<?php
// ============================================================
// LOGIN PAGE PATCH – updated $portals array for login.php
// Replace the existing $portals array in login.php with this
// ============================================================
$portals = [
    'admin'               => ['label'=>'Admin Portal',             'icon'=>'fa-shield-halved',        'color'=>'bg-red-700',    'desc'=>'School administration'],
    'master'              => ['label'=>'Master / Mistress',        'icon'=>'fa-user-tie',             'color'=>'bg-purple-700', 'desc'=>'Staff account management'],
    'teacher'             => ['label'=>'Teacher Portal',           'icon'=>'fa-chalkboard-teacher',   'color'=>'bg-blue-700',   'desc'=>'Marks entry & attendance'],
    'librarian'           => ['label'=>'Librarian Portal',         'icon'=>'fa-book',                 'color'=>'bg-green-700',  'desc'=>'Students & attendance'],
    'director_studies'    => ['label'=>'Director of Studies',      'icon'=>'fa-graduation-cap',       'color'=>'bg-indigo-700', 'desc'=>'Assignments & reports'],
    'director_discipline' => ['label'=>'Director of Discipline',   'icon'=>'fa-shield-alt',           'color'=>'bg-orange-700', 'desc'=>'Conduct marks'],
    'accountant'          => ['label'=>'Accountant Portal',        'icon'=>'fa-calculator',           'color'=>'bg-yellow-700', 'desc'=>'Fee management'],
    'parent'              => ['label'=>'Parent Portal',            'icon'=>'fa-user-friends',         'color'=>'bg-teal-700',   'desc'=>"Monitor your child"],
    'student'             => ['label'=>'Student Portal',           'icon'=>'fa-user-graduate',        'color'=>'bg-cyan-700',   'desc'=>'Grades, attendance & reports'],
];

// Destination map (add to redirect_if_logged_in in auth.php):
// 'master'              => '/portal-master.php',
// 'librarian'           => '/portal-librarian.php',
// 'director_studies'    => '/portal-dos.php',
// 'director_discipline' => '/portal-dod.php',
// 'accountant'          => '/portal-accountant.php',
