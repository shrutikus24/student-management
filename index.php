<?php

require_once "config/database.php";

$search = "";

if (isset($_GET["search"])) {
    $search = trim($_GET["search"]);
}

/* Total students */
$total_result = $conn->query("SELECT COUNT(*) AS total FROM students");
$total_students = $total_result->fetch_assoc()["total"];

/* Course count */
$bca_result = $conn->query(
    "SELECT COUNT(*) AS total FROM students WHERE course = 'BCA'"
);
$bca_students = $bca_result->fetch_assoc()["total"];

/* Semester 6 count */
$sem_result = $conn->query(
    "SELECT COUNT(*) AS total FROM students WHERE semester = 6"
);
$semester_six = $sem_result->fetch_assoc()["total"];


/* Student list */

if ($search !== "") {

    $stmt = $conn->prepare(
        "SELECT * FROM students
         WHERE roll_no LIKE ?
         OR name LIKE ?
         OR email LIKE ?
         OR course LIKE ?
         ORDER BY id DESC"
    );

    $keyword = "%" . $search . "%";

    $stmt->bind_param(
        "ssss",
        $keyword,
        $keyword,
        $keyword,
        $keyword
    );

    $stmt->execute();

    $students = $stmt->get_result();

} else {

    $students = $conn->query(
        "SELECT * FROM students ORDER BY id DESC"
    );
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Student Management Dashboard</title>

    <link rel="stylesheet" href="assets/style.css">

</head>

<body>

<div class="app">


    <!-- ================= SIDEBAR ================= -->

    <aside class="sidebar">

        <div class="brand">

            <div class="brand-icon">
                🎓
            </div>

            <div>
                <strong>EduFlow</strong>
                <span>Student Portal</span>
            </div>

        </div>


        <div class="menu-title">
            MAIN MENU
        </div>


        <nav>

            <a href="index.php" class="nav-item active">
                <span>⌂</span>
                Dashboard
            </a>

            <a href="add_student.php" class="nav-item">
                <span>＋</span>
                Add Student
            </a>

            <a href="index.php" class="nav-item">
                <span>♙</span>
                Students
            </a>

        </nav>


        <div class="menu-title settings-title">
            MANAGEMENT
        </div>


        <nav>

            <a href="index.php" class="nav-item">
                <span>▦</span>
                Student Records
            </a>

            <a href="#" class="nav-item">
                <span>▤</span>
                Reports
            </a>

        </nav>


        <div class="sidebar-bottom">

            <div class="help-card">

                <div class="help-icon">
                    ?
                </div>

                <strong>Need help?</strong>

                <p>
                    Manage your student records easily.
                </p>

            </div>

        </div>

    </aside>


    <!-- ================= MAIN ================= -->

    <main class="main">


        <!-- TOP BAR -->

        <header class="topbar">

            <div>

                <h1>
                    Good afternoon 👋
                </h1>

                <p>
                    Here's what's happening with your students today.
                </p>

            </div>


            <div class="top-actions">

                <button class="icon-btn">
                    ?
                </button>

                <button class="icon-btn notification">
                    ♧
                    <span></span>
                </button>


                <div class="admin-profile">

                    <div class="admin-avatar">
                        A
                    </div>

                    <div>

                        <strong>
                            Admin
                        </strong>

                        <small>
                            Administrator
                        </small>

                    </div>

                </div>

            </div>

        </header>


        <!-- ================= STAT CARDS ================= -->

        <section class="stats">


            <div class="stat-card yellow">

                <div>

                    <p>Total Students</p>

                    <h2>
                        <?php echo $total_students; ?>
                    </h2>

                    <small>
                        All registered students
                    </small>

                </div>

                <div class="stat-icon">
                    ♙
                </div>

            </div>


            <div class="stat-card blue">

                <div>

                    <p>BCA Students</p>

                    <h2>
                        <?php echo $bca_students; ?>
                    </h2>

                    <small>
                        Currently enrolled
                    </small>

                </div>

                <div class="stat-icon">
                    ◈
                </div>

            </div>


            <div class="stat-card purple">

                <div>

                    <p>Semester 6</p>

                    <h2>
                        <?php echo $semester_six; ?>
                    </h2>

                    <small>
                        Students in semester 6
                    </small>

                </div>

                <div class="stat-icon">
                    ◷
                </div>

            </div>


        </section>


        <!-- ================= CONTENT ================= -->

        <section class="dashboard-grid">


            <!-- LEFT -->

            <div class="left-content">


                <!-- SEARCH + STUDENTS -->

                <div class="card students-card">

                    <div class="card-heading">

                        <div>

                            <h2>
                                Student Records
                            </h2>

                            <p>
                                Manage all registered students
                            </p>

                        </div>


                        <a href="add_student.php"
                           class="primary-btn">

                            + Add Student

                        </a>

                    </div>


                    <!-- SEARCH -->

                    <form method="GET" class="search-form">

                        <div class="search-input">

                            <span>
                                ⌕
                            </span>

                            <input
                                type="text"
                                name="search"
                                placeholder="Search students..."
                                value="<?php echo htmlspecialchars($search); ?>"
                            >

                        </div>


                        <button type="submit"
                                class="search-btn">

                            Search

                        </button>


                        <?php if ($search !== ""): ?>

                            <a href="index.php"
                               class="clear-search">

                                Clear

                            </a>

                        <?php endif; ?>

                    </form>


                    <!-- TABLE -->

                    <div class="table-wrapper">

                        <table>

                            <thead>

                                <tr>

                                    <th>Student</th>

                                    <th>Roll No.</th>

                                    <th>Course</th>

                                    <th>Semester</th>

                                    <th>Actions</th>

                                </tr>

                            </thead>


                            <tbody>

                            <?php if ($students->num_rows > 0): ?>


                                <?php while ($student = $students->fetch_assoc()): ?>

                                    <?php

                                    $student_initial =
                                        strtoupper(
                                            substr(
                                                $student["name"],
                                                0,
                                                1
                                            )
                                        );

                                    ?>


                                    <tr>


                                        <td>

                                            <div class="student-cell">

                                                <div class="student-avatar">
                                                    <?php
                                                    echo htmlspecialchars(
                                                        $student_initial
                                                    );
                                                    ?>
                                                </div>

                                                <div>

                                                    <strong>
                                                        <?php
                                                        echo htmlspecialchars(
                                                            $student["name"]
                                                        );
                                                        ?>
                                                    </strong>

                                                    <small>
                                                        <?php
                                                        echo htmlspecialchars(
                                                            $student["email"]
                                                        );
                                                        ?>
                                                    </small>

                                                </div>

                                            </div>

                                        </td>


                                        <td>

                                            <span class="roll-badge">
                                                <?php
                                                echo htmlspecialchars(
                                                    $student["roll_no"]
                                                );
                                                ?>
                                            </span>

                                        </td>


                                        <td>
                                            <?php
                                            echo htmlspecialchars(
                                                $student["course"]
                                            );
                                            ?>
                                        </td>


                                        <td>

                                            <span class="semester-badge">
                                                Sem <?php
                                                echo htmlspecialchars(
                                                    $student["semester"]
                                                );
                                                ?>
                                            </span>

                                        </td>


                                        <td>

                                            <div class="table-actions">

                                                <a
                                                    href="edit_student.php?id=<?php echo $student["id"]; ?>"
                                                    title="Edit"
                                                >
                                                    ✎
                                                </a>

                                                <a
                                                    href="delete_student.php?id=<?php echo $student["id"]; ?>"
                                                    class="delete-action"
                                                    title="Delete"
                                                    onclick="return confirm('Are you sure you want to delete this student?');"
                                                >
                                                    ×
                                                </a>

                                            </div>

                                        </td>


                                    </tr>

                                <?php endwhile; ?>


                            <?php else: ?>

                                <tr>

                                    <td colspan="5">

                                        <div class="empty">

                                            <div>
                                                🎓
                                            </div>

                                            <strong>
                                                No students found
                                            </strong>

                                            <p>
                                                Add your first student to get started.
                                            </p>

                                        </div>

                                    </td>

                                </tr>

                            <?php endif; ?>

                            </tbody>

                        </table>

                    </div>

                </div>


            </div>


            <!-- RIGHT PANEL -->

            <aside class="right-panel">


                <!-- ADMIN CARD -->

                <div class="card admin-card">

                    <div class="admin-big-avatar">
                        A
                    </div>

                    <h2>
                        Administrator
                    </h2>

                    <p>
                        Student Management System
                    </p>

                    <span class="status">
                        ● System Active
                    </span>

                </div>


                <!-- QUICK ACTIONS -->

                <div class="card quick-card">

                    <div class="card-heading">

                        <div>

                            <h2>
                                Quick Actions
                            </h2>

                            <p>
                                Common tasks
                            </p>

                        </div>

                    </div>


                    <a href="add_student.php"
                       class="quick-action">

                        <div class="quick-icon add">
                            +
                        </div>

                        <div>

                            <strong>
                                Add Student
                            </strong>

                            <small>
                                Create a new student record
                            </small>

                        </div>

                        <span>
                            →
                        </span>

                    </a>


                    <a href="index.php"
                       class="quick-action">

                        <div class="quick-icon records">
                            ≡
                        </div>

                        <div>

                            <strong>
                                View Records
                            </strong>

                            <small>
                                Browse all students
                            </small>

                        </div>

                        <span>
                            →
                        </span>

                    </a>


                </div>


                <!-- SUMMARY -->

                <div class="card summary-card">

                    <div class="card-heading">

                        <div>

                            <h2>
                                Overview
                            </h2>

                            <p>
                                Current database summary
                            </p>

                        </div>

                    </div>


                    <div class="summary-row">

                        <span>
                            Total records
                        </span>

                        <strong>
                            <?php echo $total_students; ?>
                        </strong>

                    </div>


                    <div class="summary-row">

                        <span>
                            BCA students
                        </span>

                        <strong>
                            <?php echo $bca_students; ?>
                        </strong>

                    </div>


                    <div class="summary-row">

                        <span>
                            Semester 6
                        </span>

                        <strong>
                            <?php echo $semester_six; ?>
                        </strong>

                    </div>


                </div>


            </aside>


        </section>

    </main>

</div>

</body>

</html>