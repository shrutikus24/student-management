<?php

require_once "config/database.php";

$error = "";

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


        /* ================= DUPLICATE CHECK ================= */

        $check = $conn->prepare(
            "SELECT id FROM students WHERE roll_no = ?"
        );

        $check->bind_param("s", $roll_no);

        $check->execute();

        $result = $check->get_result();


        if ($result->num_rows > 0) {

            $error = "This roll number already exists.";

        } else {


            /* ================= INSERT ================= */

            $stmt = $conn->prepare(
                "INSERT INTO students
                (roll_no, name, email, phone, course, semester)
                VALUES (?, ?, ?, ?, ?, ?)"
            );

            $stmt->bind_param(
                "sssssi",
                $roll_no,
                $name,
                $email,
                $phone,
                $course,
                $semester
            );


            if ($stmt->execute()) {

                header("Location: index.php");
                exit;

            } else {

                $error = "Unable to add student. Please try again.";

            }

            $stmt->close();
        }

        $check->close();
    }
}

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Add Student | EduFlow</title>

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

        .save-btn {
            border: none;
            background: #222;
            color: white;
            padding: 12px 22px;
            border-radius: 9px;
            cursor: pointer;
            font-size: 12px;
            font-weight: 600;
        }

        .save-btn:hover {
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
            Add New Student
        </h1>

        <p class="form-description">
            Enter the student's details to create a new record.
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
                        placeholder="e.g. BCA001"
                        pattern="[A-Za-z0-9-]+"
                        maxlength="30"
                        value="<?php echo htmlspecialchars($_POST["roll_no"] ?? ""); ?>"
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
                        placeholder="Enter full name"
                        pattern="[A-Za-z ]+"
                        maxlength="100"
                        value="<?php echo htmlspecialchars($_POST["name"] ?? ""); ?>"
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
                        placeholder="student@example.com"
                        maxlength="150"
                        value="<?php echo htmlspecialchars($_POST["email"] ?? ""); ?>"
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
                        placeholder="10-digit phone number"
                        maxlength="10"
                        pattern="[0-9]{10}"
                        inputmode="numeric"
                        value="<?php echo htmlspecialchars($_POST["phone"] ?? ""); ?>"
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
                                    ($_POST["course"] ?? "") === $course_option
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
                                    (int)($_POST["semester"] ?? 0) === $i
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
                        class="save-btn">

                    Save Student

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

        alert("Name can contain only letters and spaces.");

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