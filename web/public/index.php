<?php

include '../app/vendor/autoload.php';
$db = App\Acme\Database::getInstance();

?>


<!DOCTYPE HTML>
<html>

<head>
    <style>
        .error {
            color: #FF0000;
        }
    </style>
</head>

<body>
    <?php

    // define variables and set to empty values
    $nameErr = $surnameErr = $emailErr = $genderErr = $passwordErr = "";
    $name = $email = $gender = $password = $hashedPassword = $surname = "";

    // Process the form submission
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Initialize form error flag
        $formValid = true;

        // Validate name
        if (empty($_POST["name"])) {
            $nameErr = "Name is required";
            $formValid = false;
        } else {
            $name = test_input($_POST["name"]);
            if (!preg_match("/^[a-zA-Z-' ]*$/", $name)) {
                $nameErr = "Only letters and white space allowed";
                $formValid = false;
            }
        }

        // Validate surname
        if (empty($_POST["surname"])) {
            $surnameErr = "Surname is required";
            $formValid = false;
        } else {
            $surname = test_input($_POST["surname"]);
            if (!preg_match("/^[a-zA-Z-' ]*$/", $surname)) {
                $surnameErr = "Only letters and white space allowed";
                $formValid = false;
            }
        }

        // Validate gender
        if (empty($_POST["gender"])) {
            $genderErr = "Gender is required";
            $formValid = false;
        } else {
            $gender = test_input($_POST["gender"]);
        }

        if (empty($_POST["email"])) {
            $emailErr = "Email is required";
            $formValid = false;
        } else {
            $email = test_input($_POST["email"]);
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $emailErr = "Invalid email format";
                $formValid = false;
            } else {
                // Check if email is already taken
                $sql = "SELECT id FROM users WHERE email = ?";
                $stmt = $db->getConnection()->prepare($sql);
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $stmt->store_result();

                if ($stmt->num_rows > 0) {
                    $emailErr = "Email is already taken";
                    $formValid = false;
                }

                $stmt->close();
            }
        }

        // Validate password
        if (empty($_POST["password"])) {
            $passwordErr = "Password is required";
            $formValid = false;
        } else {
            $password = test_input($_POST["password"]);
            if (!preg_match("/^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*#?&])[A-Za-z\d@$!%*#?&]{8,}$/", $password)) {
                $passwordErr = "Password must be at least 8 characters long, contain at least one letter, one number, and one special character.";
                $formValid = false;
            } else {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            }
        }

        echo "Name: " . $name . "<br>";
        echo "Surname: " . $surname . "<br>";
        echo "Email: " . $email . "<br>";
        echo "Gender: " . $gender . "<br>";
        echo "Hashed Password: " . $hashedPassword . "<br>";

        // Only insert data if all fields are valid
        if ($formValid) {
            // Prepare the SQL query
            $sql = "INSERT INTO users (name, surname, gender, email, password) VALUES (?, ?, ?, ?, ?)";
            $stmt = $db->getConnection()->prepare($sql);
            $stmt->bind_param("sssss", $name, $surname, $gender, $email, $hashedPassword);
        
            // Execute the query
            if ($stmt->execute()) {
                echo "New record created successfully!";
            } else {
                echo "Error: " . $stmt->error;
            }
        
            $stmt->close();
        }
    }

    function test_input($data)
    {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }

    ?>

    <h2>Sign Up</h2>
    <p><span class="error">* required field</span></p>
    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
        Name: <input type="text" name="name" value="<?php echo $name; ?>">
        <span class="error">*<?php echo $nameErr; ?></span>
        <br><br>
        Surname: <input type="text" name="surname" value="<?php echo $surname; ?>">
        <span class="error">*<?php echo $surnameErr; ?></span>
        <br><br>
        Gender:
        <input type="radio" name="gender" <?php if (isset($gender) && $gender == "female") echo "checked"; ?> value="female">Female
        <input type="radio" name="gender" <?php if (isset($gender) && $gender == "male") echo "checked"; ?> value="male">Male
        <input type="radio" name="gender" <?php if (isset($gender) && $gender == "other") echo "checked"; ?> value="other">Other
        <span class="error">*<?php echo $genderErr; ?></span>
        <br><br>
        E-mail: <input type="text" name="email" value="<?php echo $email; ?>">
        <span class="error">*<?php echo $emailErr; ?></span>
        <br><br>
        Password: <input type="password" name="password" value="<?php echo $password; ?>">
        <span class="error">* <?php echo $passwordErr; ?></span>
        <br><br>
        <input type="submit" name="submit" value="Submit">
    </form>

</body>

</html>