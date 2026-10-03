<?php

require_once "config/database.php";

$error = "";


/* ================= GET STUDENT ID ================= */

if (
    !isset($_GET["id"]) ||
    !is_numeric($_GET["id"])
) {

    header("Location: index.php");
    exit;

}

$id = (int)$_GET["id"];


/* ================= FETCH STUDENT ================= */

$stmt = $conn->prepare(
    "SELECT * FROM students WHERE id = ?"
);

$stmt->bind_param("i", $id);

$stmt->execute();

$result = $stmt->get_result();


if ($result->num_rows === 0) {

    header("Location: index.php");
    exit;

}

$student = $result->fetch_assoc();

$stmt->close();


/* ================= UPDATE ================= */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $roll_no = trim($_POST["roll_no"] ?? "");
    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $phone = trim($_POST["phone"] ?? "");
    $course = trim($_POST["course"] ?? "");
    $semester = (int)($_POST["semester"] ?? 0);


    /* ================= VALIDATION ================= */

    if (
        empty($roll_no) ||
        empty($name) ||
        empty($email) ||
        empty($course) ||
        $semester <= 0
    ) {

        $error = "Please fill all required fields.";

    } elseif (!preg_match("/^[A-Za-z0-9-]+$/", $roll_no)) {

        $error = "Roll number can contain only letters, numbers and hyphens.";

    } elseif (!preg_match("/^[A-Za-z ]+$/", $name)) {

        $error = "Name can contain only letters and spaces.";

    } elseif (strlen($name) < 2) {

        $error = "Name must contain at least 2 characters.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $error = "Please enter a valid email address.";

    } elseif (
        $phone !== "" &&
        !preg_match("/^[0-9]{10}$/", $phone)
    ) {

        $error = "Phone number must contain exactly 10 digits.";

    } elseif ($semester < 1 || $semester > 8) {

        $error = "Please select a valid semester.";

    } else {


        /* ================= DUPLICATE ROLL CHECK ================= */

        $check = $conn->prepare(
            "SELECT id
             FROM students
             WHERE roll_no = ?
             AND id != ?"
        );

        $check->bind_param(
            "si",
            $roll_no,
            $id
        );

        $check->execute();

        $duplicate = $check->get_result();


        if ($duplicate->num_rows > 0) {

            $error =
                "This roll number already belongs to another student.";

        } else {


            /* ================= UPDATE DATABASE ================= */

            $update = $conn->prepare(
                "UPDATE students
                 SET roll_no = ?,
                     name = ?,
                     email = ?,
                     phone = ?,
                     course = ?,
                     semester = ?
                 WHERE id = ?"
            );


            $update->bind_param(
                "sssssii",
                $roll_no,
                $name,
                $email,
                $phone,
                $course,
                $semester,
                $id
            );


            if ($update->execute()) {

                header("Location: index.php");
                exit;

            } else {

                $error =
                    "Unable to update student. Please try again.";

            }


            $update->close();

        }


        $check->close();

    }


    /* Keep entered data after validation error */

    $student["roll_no"] = $roll_no;
    $student["name"] = $name;
    $student["email"] = $email;
    $student["phone"] = $phone;
    $student["course"] = $course;
    $student["semester"] = $semester;

}

?>


<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Edit Student | EduFlow</title>

    <link rel="stylesheet"
          href="assets/style.css">


    <style>

        .form-container {
            max-width: 750px;
            margin: 40px auto;
        }

        .form-card {
            background: #f8f8f8;
            border: 1px solid #dedede;
            border-radius: 18px;
            padding: 30px;
        }

        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #666;
            font-size: 13px;
            text-decoration: none;
        }

        .back-link:hover {
            color: #222;
        }

        .form-card h1 {
            font-size: 25px;
            margin-bottom: 6px;
        }

        .form-description {
            color: #999;
            font-size: 13px;
            margin-bottom: 25px;
        }

        .error {
            background: #fee2e2;
            border: 1px solid #fecaca;
            color: #b91c1c;
            padding: 12px 15px;
            border-radius: 9px;
            margin-bottom: 20px;
            font-size: 13px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 18px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group label {
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 7px;
            color: #444;
        }

        .required {
            color: #ef4444;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 12px 13px;
            border: 1px solid #d8d8d8;
            border-radius: 9px;
            background: white;
            font-size: 13px;
            outline: none;
        }

        .form-group input:focus,
        .form-group select:focus {
            border-color: #888;
        }

        .form-hint {
            color: #999;
            font-size: 10px;
            margin-top: 5px;
        }

        .form-actions {
            display: flex;
            gap: 10px;
            margin-top: 25px;
        }

        .update-btn {
            border: none;
            background: #222;
            color: white;
            padding: 12px 22px;
            border-radius: 9px;
            cursor: pointer;
            font-size: 12px;
            font-weight: 600;
        }

        .update-btn:hover {
            background: #000;
        }

        .cancel-btn {
            background: #e7e7e7;
            color: #444;
            padding: 12px 22px;
            border-radius: 9px;
            font-size: 12px;
            font-weight: 600;
            text-decoration: none;
        }

        @media (max-width: 600px) {

            .form-grid {
                grid-template-columns: 1fr;
            }

            .form-container {
                margin: 20px auto;
            }

        }

    </style>

</head>


<body>


<div class="container form-container">


    <a href="index.php"
       class="back-link">

        ← Back to Dashboard

    </a>


    <div class="form-card">


        <h1>
            Edit Student
        </h1>

        <p class="form-description">
            Update the student's information below.
        </p>


        <?php if ($error !== ""): ?>

            <div class="error">

                ⚠

                <?php echo htmlspecialchars($error); ?>

            </div>

        <?php endif; ?>


        <form method="POST"
              onsubmit="return validateForm();">


            <div class="form-grid">


                <!-- Roll Number -->

                <div class="form-group">

                    <label>
                        Roll Number
                        <span class="required">*</span>
                    </label>

                    <input
                        type="text"
                        name="roll_no"
                        pattern="[A-Za-z0-9-]+"
                        maxlength="30"
                        value="<?php echo htmlspecialchars($student["roll_no"]); ?>"
                        required
                    >

                    <span class="form-hint">
                        Letters, numbers and hyphens only
                    </span>

                </div>


                <!-- Name -->

                <div class="form-group">

                    <label>
                        Student Name
                        <span class="required">*</span>
                    </label>

                    <input
                        type="text"
                        name="name"
                        pattern="[A-Za-z ]+"
                        maxlength="100"
                        value="<?php echo htmlspecialchars($student["name"]); ?>"
                        required
                    >

                </div>


                <!-- Email -->

                <div class="form-group">

                    <label>
                        Email
                        <span class="required">*</span>
                    </label>

                    <input
                        type="email"
                        name="email"
                        maxlength="150"
                        value="<?php echo htmlspecialchars($student["email"]); ?>"
                        required
                    >

                </div>


                <!-- Phone -->

                <div class="form-group">

                    <label>
                        Phone
                    </label>

                    <input
                        type="text"
                        name="phone"
                        maxlength="10"
                        pattern="[0-9]{10}"
                        inputmode="numeric"
                        value="<?php echo htmlspecialchars($student["phone"] ?? ""); ?>"
                    >

                    <span class="form-hint">
                        Optional — exactly 10 digits
                    </span>

                </div>


                <!-- Course -->

                <div class="form-group">

                    <label>
                        Course
                        <span class="required">*</span>
                    </label>

                    <select name="course"
                            required>

                        <option value="">
                            Select Course
                        </option>

                        <?php

                        $courses = [
                            "BCA",
                            "BBA",
                            "B.Tech",
                            "MCA",
                            "MBA"
                        ];

                        foreach ($courses as $course_option):

                        ?>

                            <option
                                value="<?php echo $course_option; ?>"
                                <?php
                                echo (
                                    $student["course"] === $course_option
                                )
                                ? "selected"
                                : "";
                                ?>
                            >

                                <?php echo $course_option; ?>

                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>


                <!-- Semester -->

                <div class="form-group">

                    <label>
                        Semester
                        <span class="required">*</span>
                    </label>

                    <select name="semester"
                            required>

                        <option value="">
                            Select Semester
                        </option>

                        <?php for ($i = 1; $i <= 8; $i++): ?>

                            <option
                                value="<?php echo $i; ?>"
                                <?php
                                echo (
                                    (int)$student["semester"] === $i
                                )
                                ? "selected"
                                : "";
                                ?>
                            >

                                Semester <?php echo $i; ?>

                            </option>

                        <?php endfor; ?>

                    </select>

                </div>


            </div>


            <div class="form-actions">

                <button type="submit"
                        class="update-btn">

                    Update Student

                </button>

                <a href="index.php"
                   class="cancel-btn">

                    Cancel

                </a>

            </div>


        </form>


    </div>


</div>


<script>

function validateForm() {

    const phone =
        document.querySelector('[name="phone"]').value.trim();

    const name =
        document.querySelector('[name="name"]').value.trim();

    const roll =
        document.querySelector('[name="roll_no"]').value.trim();


    if (!/^[A-Za-z ]+$/.test(name)) {

        alert(
            "Name can contain only letters and spaces."
        );

        return false;

    }


    if (!/^[A-Za-z0-9-]+$/.test(roll)) {

        alert(
            "Roll number can contain only letters, numbers and hyphens."
        );

        return false;

    }


    if (
        phone !== "" &&
        !/^[0-9]{10}$/.test(phone)
    ) {

        alert(
            "Phone number must contain exactly 10 digits."
        );

        return false;

    }


    return true;

}

</script>


</body>

</html>