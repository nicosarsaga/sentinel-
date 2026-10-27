<?php

session_start();

require_once __DIR__ . "/config/database.php";
require_once __DIR__ . "/config/mail.php";

$error = "";
$success = "";

/*
|--------------------------------------------------------------------------
| Process Login
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Safely retrieve form values
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    /*
    |--------------------------------------------------------------------------
    | Validate Input
    |--------------------------------------------------------------------------
    */

    if ($email === "" || $password === "") {

        $error = "Email and password are required.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $error = "Please enter a valid email address.";

    } else {

        /*
        |--------------------------------------------------------------------------
        | Find User
        |--------------------------------------------------------------------------
        */

        $stmt = $conn->prepare(
            "SELECT
                id,
                username,
                firstname,
                lastname,
                email,
                password
             FROM users
             WHERE email = ?
             LIMIT 1"
        );

        if (!$stmt) {

            $error = "Database error. Please try again.";

        } else {

            $stmt->bind_param("s", $email);

            $stmt->execute();

            $result = $stmt->get_result();

            /*
            |--------------------------------------------------------------------------
            | Check User
            |--------------------------------------------------------------------------
            */

            if ($result->num_rows !== 1) {

                $error = "Invalid email or password.";

            } else {

                $user = $result->fetch_assoc();

                /*
                |--------------------------------------------------------------------------
                | Verify Password
                |--------------------------------------------------------------------------
                */

                if (
                    !password_verify(
                        $password,
                        $user["password"]
                    )
                ) {

                    $error = "Invalid email or password.";

                } else {

                    /*
                    |--------------------------------------------------------------------------
                    | Password Correct
                    |--------------------------------------------------------------------------
                    |
                    | Generate a 6-digit OTP
                    |
                    */

                    $otp = (string) random_int(
                        100000,
                        999999
                    );

                    /*
                    |--------------------------------------------------------------------------
                    | Hash OTP
                    |--------------------------------------------------------------------------
                    */

                    $otpHash = password_hash(
                        $otp,
                        PASSWORD_DEFAULT
                    );

                    /*
                    |--------------------------------------------------------------------------
                    | OTP Expiration
                    |--------------------------------------------------------------------------
                    |
                    | OTP is valid for 5 minutes.
                    |
                    */

                    $expiresAt = date(
                        "Y-m-d H:i:s",
                        time() + 300
                    );

                    /*
                    |--------------------------------------------------------------------------
                    | Remove Previous OTP
                    |--------------------------------------------------------------------------
                    */

                    $delete = $conn->prepare(
                        "DELETE FROM login_otps
                         WHERE user_id = ?"
                    );

                    if ($delete) {

                        $delete->bind_param(
                            "i",
                            $user["id"]
                        );

                        $delete->execute();

                        $delete->close();
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Store New OTP
                    |--------------------------------------------------------------------------
                    */

                    $insert = $conn->prepare(
                        "INSERT INTO login_otps
                        (
                            user_id,
                            otp_hash,
                            expires_at,
                            attempts
                        )
                        VALUES (?, ?, ?, 0)"
                    );

                    if (!$insert) {

                        $error =
                            "Unable to create OTP.";

                    } else {

                        $insert->bind_param(
                            "iss",
                            $user["id"],
                            $otpHash,
                            $expiresAt
                        );

                        if (!$insert->execute()) {

                            $error =
                                "Unable to save OTP.";

                        } else {

                            /*
                            |--------------------------------------------------------------------------
                            | Send OTP Through Gmail
                            |--------------------------------------------------------------------------
                            */

                            $emailSent = sendOTPEmail(
                                $user["email"],
                                $user["firstname"],
                                $otp
                            );

                            if ($emailSent) {

                                /*
                                |--------------------------------------------------------------------------
                                | Store Temporary OTP Session
                                |--------------------------------------------------------------------------
                                */

                                $_SESSION["otp_user_id"] =
                                    $user["id"];

                                $_SESSION["otp_email"] =
                                    $user["email"];

                                /*
                                |--------------------------------------------------------------------------
                                | Redirect to OTP Verification
                                |--------------------------------------------------------------------------
                                */

                                header(
                                    "Location: verify-otp.php"
                                );

                                exit;

                            } else {

                                /*
                                |--------------------------------------------------------------------------
                                | Email Failed
                                |--------------------------------------------------------------------------
                                */

                                $error =
                                    "Unable to send OTP. " .
                                    "Please check your Gmail SMTP configuration.";
                            }
                        }

                        $insert->close();
                    }
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

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Login | PHP OTP Authentication
    </title>

    <!-- Tailwind CSS -->

    <script src="https://cdn.tailwindcss.com"></script>

</head>

<body class="bg-gray-100 min-h-screen">

    <!--
    |--------------------------------------------------------------------------
    | Main Container
    |--------------------------------------------------------------------------
    -->

    <div
        class="
            min-h-screen
            flex
            items-center
            justify-center
            px-4
            py-10
        "
    >

        <div class="w-full max-w-md">

            <!--
            |--------------------------------------------------------------------------
            | Login Card
            |--------------------------------------------------------------------------
            -->

            <div
                class="
                    bg-white
                    rounded-2xl
                    shadow-xl
                    p-8
                "
            >

                <!-- Logo -->

                <div class="text-center mb-8">

                    <div
                        class="
                            mx-auto
                            w-16
                            h-16
                            bg-blue-100
                            rounded-full
                            flex
                            items-center
                            justify-center
                            text-3xl
                        "
                    >
                        🔐
                    </div>

                    <h1
                        class="
                            text-3xl
                            font-bold
                            text-gray-800
                            mt-4
                        "
                    >
                        Welcome Back
                    </h1>

                    <p
                        class="
                            text-gray-500
                            mt-2
                        "
                    >
                        Sign in to your account
                    </p>

                </div>

                <!--
                |--------------------------------------------------------------------------
                | Error Message
                |--------------------------------------------------------------------------
                -->

                <?php if ($error !== ""): ?>

                    <div
                        class="
                            bg-red-50
                            border
                            border-red-200
                            text-red-700
                            px-4
                            py-3
                            rounded-lg
                            mb-5
                        "
                    >

                        <div class="flex items-start gap-3">

                            <span class="text-lg">
                                ⚠
                            </span>

                            <div>

                                <p class="font-medium">
                                    Login Error
                                </p>

                                <p class="text-sm mt-1">
                                    <?= htmlspecialchars(
                                        $error
                                    ) ?>
                                </p>

                            </div>

                        </div>

                    </div>

                <?php endif; ?>

                <!--
                |--------------------------------------------------------------------------
                | Success Message
                |--------------------------------------------------------------------------
                -->

                <?php if ($success !== ""): ?>

                    <div
                        class="
                            bg-green-50
                            border
                            border-green-200
                            text-green-700
                            px-4
                            py-3
                            rounded-lg
                            mb-5
                        "
                    >

                        <?= htmlspecialchars(
                            $success
                        ) ?>

                    </div>

                <?php endif; ?>

                <!--
                |--------------------------------------------------------------------------
                | Login Form
                |--------------------------------------------------------------------------
                -->

                <form
                    method="POST"
                    action="login.php"
                    class="space-y-5"
                >

                    <!-- Email -->

                    <div>

                        <label
                            for="email"
                            class="
                                block
                                text-sm
                                font-medium
                                text-gray-700
                                mb-2
                            "
                        >
                            Email Address
                        </label>

                        <input
                            type="email"
                            id="email"
                            name="email"
                            value="<?= htmlspecialchars(
                                $_POST["email"] ?? ""
                            ) ?>"
                            autocomplete="email"
                            placeholder="you@example.com"
                            required
                            class="
                                w-full
                                border
                                border-gray-300
                                rounded-lg
                                px-4
                                py-3
                                text-gray-800
                                focus:outline-none
                                focus:ring-2
                                focus:ring-blue-500
                                focus:border-blue-500
                            "
                        >

                    </div>

                    <!-- Password -->

                    <div>

                        <div
                            class="
                                flex
                                justify-between
                                items-center
                                mb-2
                            "
                        >

                            <label
                                for="password"
                                class="
                                    block
                                    text-sm
                                    font-medium
                                    text-gray-700
                                "
                            >
                                Password
                            </label>

                        </div>

                        <input
                            type="password"
                            id="password"
                            name="password"
                            autocomplete="current-password"
                            placeholder="Enter your password"
                            required
                            class="
                                w-full
                                border
                                border-gray-300
                                rounded-lg
                                px-4
                                py-3
                                text-gray-800
                                focus:outline-none
                                focus:ring-2
                                focus:ring-blue-500
                                focus:border-blue-500
                            "
                        >

                    </div>

                    <!-- Login Button -->

                    <button
                        type="submit"
                        class="
                            w-full
                            bg-blue-600
                            hover:bg-blue-700
                            active:bg-blue-800
                            text-white
                            font-semibold
                            py-3
                            px-4
                            rounded-lg
                            transition
                            duration-200
                            focus:outline-none
                            focus:ring-2
                            focus:ring-blue-500
                            focus:ring-offset-2
                        "
                    >

                        Login

                    </button>

                </form>

                <!--
                |--------------------------------------------------------------------------
                | Security Information
                |--------------------------------------------------------------------------
                -->

                <div
                    class="
                        mt-6
                        p-4
                        bg-blue-50
                        border
                        border-blue-100
                        rounded-lg
                    "
                >

                    <div class="flex gap-3">

                        <span class="text-xl">
                            🛡️
                        </span>

                        <div>

                            <p
                                class="
                                    text-sm
                                    font-semibold
                                    text-blue-800
                                "
                            >
                                Two-Factor Authentication
                            </p>

                            <p
                                class="
                                    text-xs
                                    text-blue-700
                                    mt-1
                                "
                            >
                                After entering your password,
                                a 6-digit OTP will be sent to
                                your registered email address.
                            </p>

                        </div>

                    </div>

                </div>

                <!--
                |--------------------------------------------------------------------------
                | Register Link
                |--------------------------------------------------------------------------
                -->

                <div
                    class="
                        text-center
                        mt-6
                        text-sm
                    "
                >

                    <span class="text-gray-500">
                        Don't have an account?
                    </span>

                    <a
                        href="register.php"
                        class="
                            text-blue-600
                            font-semibold
                            hover:text-blue-800
                            hover:underline
                            ml-1
                        "
                    >
                        Create an account
                    </a>

                </div>

            </div>

            <!-- Footer -->

            <p
                class="
                    text-center
                    text-xs
                    text-gray-400
                    mt-6
                "
            >
                PHP Login System &bull;
                Email OTP Authentication
            </p>

        </div>

    </div>

</body>

</html>