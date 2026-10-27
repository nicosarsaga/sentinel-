
<?php

session_start();

require_once "config/database.php";

$error = "";
$success = "";

/*
|--------------------------------------------------------------------------
| REGISTER ACCOUNT
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $username = trim($_POST["username"] ?? "");
    $firstname = trim($_POST["firstname"] ?? "");
    $lastname = trim($_POST["lastname"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";
    $confirm_password = $_POST["confirm_password"] ?? "";

    /*
    |--------------------------------------------------------------------------
    | VALIDATION
    |--------------------------------------------------------------------------
    */

    if (
        $username === "" ||
        $firstname === "" ||
        $lastname === "" ||
        $email === "" ||
        $password === "" ||
        $confirm_password === ""
    ) {
        $error = "Please fill in all fields.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";

    } elseif (strlen($password) < 8) {
        $error = "Password must be at least 8 characters.";

    } elseif (!preg_match('/[A-Z]/', $password)) {
        $error = "Password must contain at least one uppercase letter.";

    } elseif (!preg_match('/[a-z]/', $password)) {
        $error = "Password must contain at least one lowercase letter.";

    } elseif (!preg_match('/[0-9]/', $password)) {
        $error = "Password must contain at least one number.";

    } elseif (!preg_match('/[^a-zA-Z0-9]/', $password)) {
        $error = "Password must contain at least one special character.";

    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";

    } else {

        /*
        |--------------------------------------------------------------------------
        | CHECK DUPLICATE USERNAME OR EMAIL
        |--------------------------------------------------------------------------
        */

        $stmt = $conn->prepare(
            "SELECT id FROM users
             WHERE username = ? OR email = ?
             LIMIT 1"
        );

        if (!$stmt) {
            $error = "Something went wrong. Please try again.";

        } else {

            $stmt->bind_param("ss", $username, $email);
            $stmt->execute();

            $result = $stmt->get_result();

            if ($result->num_rows > 0) {

                $error = "Username or email already exists.";

            } else {

                /*
                |--------------------------------------------------------------------------
                | HASH PASSWORD
                |--------------------------------------------------------------------------
                */

                $password_hash = password_hash(
                    $password,
                    PASSWORD_DEFAULT
                );

                /*
                |--------------------------------------------------------------------------
                | INSERT USER
                |--------------------------------------------------------------------------
                */

                $insert = $conn->prepare(
                    "INSERT INTO users
                    (username, firstname, lastname, email, password)
                    VALUES (?, ?, ?, ?, ?)"
                );

                if (!$insert) {

                    $error = "Unable to create account. Please try again.";

                } else {

                    $insert->bind_param(
                        "sssss",
                        $username,
                        $firstname,
                        $lastname,
                        $email,
                        $password_hash
                    );

                    if ($insert->execute()) {

                        $success = "Account created successfully! You can now login.";

                    } elseif ($insert->errno === 1062) {

                        $error = "Username or email already exists.";

                    } else {

                        $error = "Registration failed. Please try again.";

                    }

                    $insert->close();
                }
            }

            $stmt->close();
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Create Account - SENTINEL</title>

    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 min-h-screen">

<div class="min-h-screen flex items-center justify-center px-4 py-10">

    <div class="w-full max-w-lg">

        <div class="bg-white rounded-2xl shadow-xl p-8">

            <!-- HEADER -->

            <div class="text-center mb-8">

                <div class="text-5xl mb-3">🛡️</div>

                <h1 class="text-3xl font-bold text-gray-800">
                    Create Account
                </h1>

                <p class="text-gray-500 mt-2">
                    Create your SENTINEL account
                </p>

            </div>

            <!-- ERROR MESSAGE -->

            <?php if ($error !== ""): ?>

                <div class="bg-red-100 border border-red-300 text-red-700 px-4 py-3 rounded-lg mb-5">

                    <?= htmlspecialchars(
                        $error,
                        ENT_QUOTES,
                        "UTF-8"
                    ) ?>

                </div>

            <?php endif; ?>

            <!-- SUCCESS MESSAGE -->

            <?php if ($success !== ""): ?>

                <div class="bg-green-100 border border-green-300 text-green-700 px-4 py-3 rounded-lg mb-5">

                    <?= htmlspecialchars(
                        $success,
                        ENT_QUOTES,
                        "UTF-8"
                    ) ?>

                    <p class="mt-2">
                        <a
                            href="login.php"
                            class="font-semibold underline"
                        >
                            Go to Login
                        </a>
                    </p>

                </div>

            <?php endif; ?>

            <!-- REGISTRATION FORM -->

            <?php if ($success === ""): ?>

                <form method="POST" class="space-y-4" id="registerForm">

                    <!-- USERNAME -->

                    <div>
                        <label
                            for="username"
                            class="block text-sm font-medium mb-1"
                        >
                            Username
                        </label>

                        <input
                            type="text"
                            id="username"
                            name="username"
                            autocomplete="username"
                            value="<?= htmlspecialchars(
                                $_POST["username"] ?? "",
                                ENT_QUOTES,
                                "UTF-8"
                            ) ?>"
                            required
                            class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                            placeholder="Enter username"
                        >
                    </div>

                    <!-- FIRST NAME -->

                    <div>
                        <label
                            for="firstname"
                            class="block text-sm font-medium mb-1"
                        >
                            First Name
                        </label>

                        <input
                            type="text"
                            id="firstname"
                            name="firstname"
                            autocomplete="given-name"
                            value="<?= htmlspecialchars(
                                $_POST["firstname"] ?? "",
                                ENT_QUOTES,
                                "UTF-8"
                            ) ?>"
                            required
                            class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                            placeholder="Enter first name"
                        >
                    </div>

                    <!-- LAST NAME -->

                    <div>
                        <label
                            for="lastname"
                            class="block text-sm font-medium mb-1"
                        >
                            Last Name
                        </label>

                        <input
                            type="text"
                            id="lastname"
                            name="lastname"
                            autocomplete="family-name"
                            value="<?= htmlspecialchars(
                                $_POST["lastname"] ?? "",
                                ENT_QUOTES,
                                "UTF-8"
                            ) ?>"
                            required
                            class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                            placeholder="Enter last name"
                        >
                    </div>

                    <!-- EMAIL -->

                    <div>
                        <label
                            for="email"
                            class="block text-sm font-medium mb-1"
                        >
                            Email Address
                        </label>

                        <input
                            type="email"
                            id="email"
                            name="email"
                            autocomplete="email"
                            value="<?= htmlspecialchars(
                                $_POST["email"] ?? "",
                                ENT_QUOTES,
                                "UTF-8"
                            ) ?>"
                            required
                            class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                            placeholder="Enter email address"
                        >
                    </div>

                    <!-- PASSWORD -->

                    <div>

                        <label
                            for="password"
                            class="block text-sm font-medium mb-1"
                        >
                            Password
                        </label>

                        <input
                            type="password"
                            id="password"
                            name="password"
                            autocomplete="new-password"
                            minlength="8"
                            required
                            class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                            placeholder="Create a password"
                        >

                        <!-- LIVE PASSWORD REQUIREMENTS -->

                        <div class="mt-3 space-y-2 text-sm">

                            <p class="font-medium text-gray-600">
                                Password requirements:
                            </p>

                            <p id="req-length" class="text-gray-500">
                                ○ At least 8 characters
                            </p>

                            <p id="req-uppercase" class="text-gray-500">
                                ○ One uppercase letter (A-Z)
                            </p>

                            <p id="req-lowercase" class="text-gray-500">
                                ○ One lowercase letter (a-z)
                            </p>

                            <p id="req-number" class="text-gray-500">
                                ○ One number (0-9)
                            </p>

                            <p id="req-special" class="text-gray-500">
                                ○ One special character (!@#$%^&*)
                            </p>

                        </div>

                    </div>

                    <!-- CONFIRM PASSWORD -->

                    <div>

                        <label
                            for="confirm_password"
                            class="block text-sm font-medium mb-1"
                        >
                            Confirm Password
                        </label>

                        <input
                            type="password"
                            id="confirm_password"
                            name="confirm_password"
                            autocomplete="new-password"
                            minlength="8"
                            required
                            class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                            placeholder="Re-enter your password"
                        >

                        <p
                            id="password-match"
                            class="text-sm mt-2 text-gray-500"
                            aria-live="polite"
                        >
                        </p>

                    </div>

                    <!-- CREATE ACCOUNT BUTTON -->

                    <button
                        type="submit"
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-lg transition duration-200"
                    >
                        Create Account
                    </button>

                </form>

            <?php endif; ?>

            <!-- LOGIN LINK -->

            <div class="text-center mt-6">

                <span class="text-gray-500">
                    Already have an account?
                </span>

                <a
                    href="login.php"
                    class="text-blue-600 font-semibold hover:underline"
                >
                    Login
                </a>

            </div>

        </div>

    </div>

</div>

<!-- LIVE PASSWORD VALIDATION -->

<script>
const passwordInput = document.getElementById("password");
const confirmInput = document.getElementById("confirm_password");
const registerForm = document.getElementById("registerForm");

const requirements = {
    length: {
        element: document.getElementById("req-length"),
        test: password => password.length >= 8,
        text: "At least 8 characters"
    },

    uppercase: {
        element: document.getElementById("req-uppercase"),
        test: password => /[A-Z]/.test(password),
        text: "One uppercase letter (A-Z)"
    },

    lowercase: {
        element: document.getElementById("req-lowercase"),
        test: password => /[a-z]/.test(password),
        text: "One lowercase letter (a-z)"
    },

    number: {
        element: document.getElementById("req-number"),
        test: password => /[0-9]/.test(password),
        text: "One number (0-9)"
    },

    special: {
        element: document.getElementById("req-special"),
        test: password => /[^a-zA-Z0-9]/.test(password),
        text: "One special character (!@#$%^&*)"
    }
};

function updatePasswordRequirements() {

    const password = passwordInput.value;

    for (const item of Object.values(requirements)) {

        const passed = item.test(password);

        item.element.textContent =
            (passed ? "✓ " : "○ ") + item.text;

        item.element.classList.toggle(
            "text-green-600",
            passed
        );

        item.element.classList.toggle(
            "font-medium",
            passed
        );

        item.element.classList.toggle(
            "text-gray-500",
            !passed
        );
    }

    updatePasswordMatch();
}

function updatePasswordMatch() {

    const message = document.getElementById("password-match");

    const password = passwordInput.value;
    const confirmation = confirmInput.value;

    message.classList.remove(
        "text-green-600",
        "text-red-600",
        "text-gray-500",
        "font-medium"
    );

    if (confirmation.length === 0) {

        message.textContent =
            "Re-enter your password to confirm.";

        message.classList.add("text-gray-500");

    } else if (password === confirmation) {

        message.textContent = "✓ Passwords match!";

        message.classList.add(
            "text-green-600",
            "font-medium"
        );

    } else {

        message.textContent = "✗ Passwords do not match.";

        message.classList.add(
            "text-red-600",
            "font-medium"
        );
    }
}

if (passwordInput && confirmInput && registerForm) {

    passwordInput.addEventListener(
        "input",
        updatePasswordRequirements
    );

    confirmInput.addEventListener(
        "input",
        updatePasswordMatch
    );

    // Prevent submission if password requirements are not met.
    registerForm.addEventListener("submit", function (event) {

        updatePasswordRequirements();
        updatePasswordMatch();

        const allRequirementsPassed =
            Object.values(requirements).every(
                item => item.test(passwordInput.value)
            );

        const passwordsMatch =
            passwordInput.value === confirmInput.value;

        if (!allRequirementsPassed || !passwordsMatch) {

            event.preventDefault();

            if (!allRequirementsPassed) {
                passwordInput.focus();
            } else {
                confirmInput.focus();
            }
        }
    });
}
</script>

</body>
</html>
