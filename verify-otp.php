<?php

session_start();

require_once "config/database.php";

if (
    !isset($_SESSION["otp_user_id"])
) {

    header("Location: login.php");
    exit;
}

$error = "";

$user_id = $_SESSION["otp_user_id"];

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $otp = trim($_POST["otp"]);

    if (!preg_match('/^[0-9]{6}$/', $otp)) {

        $error =
            "Please enter a valid 6-digit OTP.";

    } else {

        $stmt = $conn->prepare(
            "SELECT
                id,
                otp_hash,
                expires_at,
                attempts
             FROM login_otps
             WHERE user_id = ?
             AND used_at IS NULL
             ORDER BY id DESC
             LIMIT 1"
        );

        $stmt->bind_param(
            "i",
            $user_id
        );

        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows === 1) {

            $otpData =
                $result->fetch_assoc();

            // Maximum attempts
            if (
                $otpData["attempts"] >= 5
            ) {

                $error =
                    "Too many incorrect attempts. " .
                    "Please login again.";

            // Check expiration
            } elseif (
                strtotime(
                    $otpData["expires_at"]
                ) < time()
            ) {

                $error =
                    "OTP has expired. " .
                    "Please login again.";

            // Check OTP
            } elseif (
                password_verify(
                    $otp,
                    $otpData["otp_hash"]
                )
            ) {

                // Mark OTP as used
                $update = $conn->prepare(
                    "UPDATE login_otps
                     SET used_at = NOW()
                     WHERE id = ?"
                );

                $update->bind_param(
                    "i",
                    $otpData["id"]
                );

                $update->execute();

                // Get user
                $userStmt = $conn->prepare(
                    "SELECT
                        id,
                        username,
                        firstname,
                        lastname,
                        email
                     FROM users
                     WHERE id = ?
                     LIMIT 1"
                );

                $userStmt->bind_param(
                    "i",
                    $user_id
                );

                $userStmt->execute();

                $userResult =
                    $userStmt->get_result();

                if (
                    $userResult->num_rows === 1
                ) {

                    $user =
                        $userResult->fetch_assoc();

                    // Prevent session fixation
                    session_regenerate_id(true);

                    // Authenticated session
                    $_SESSION["logged_in"] =
                        true;

                    $_SESSION["user_id"] =
                        $user["id"];

                    $_SESSION["username"] =
                        $user["username"];

                    $_SESSION["firstname"] =
                        $user["firstname"];

                    $_SESSION["lastname"] =
                        $user["lastname"];

                    $_SESSION["email"] =
                        $user["email"];

                    // Remove OTP session
                    unset(
                        $_SESSION["otp_user_id"]
                    );

                    unset(
                        $_SESSION["otp_email"]
                    );

                    header(
                        "Location: dashboard.php"
                    );

                    exit;
                }

            } else {

                // Increase attempts
                $update = $conn->prepare(
                    "UPDATE login_otps
                     SET attempts = attempts + 1
                     WHERE id = ?"
                );

                $update->bind_param(
                    "i",
                    $otpData["id"]
                );

                $update->execute();

                $error =
                    "Incorrect OTP.";
            }

        } else {

            $error =
                "No active OTP found.";
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

    <title>Verify OTP</title>

    <script src="https://cdn.tailwindcss.com"></script>

</head>

<body class="bg-gray-100 min-h-screen">

<div class="min-h-screen flex items-center justify-center px-4">

    <div class="w-full max-w-md">

        <div class="bg-white rounded-2xl shadow-xl p-8">

            <div class="text-center mb-8">

                <div class="text-5xl mb-3">
                    📧
                </div>

                <h1 class="text-3xl font-bold">
                    Verify Your Login
                </h1>

                <p class="text-gray-500 mt-3">
                    We sent a 6-digit verification
                    code to your email.
                </p>

                <p class="text-sm text-blue-600 mt-2">
                    <?= htmlspecialchars(
                        $_SESSION["otp_email"]
                    ) ?>
                </p>

            </div>

            <?php if ($error): ?>

                <div class="
                    bg-red-100
                    border border-red-300
                    text-red-700
                    px-4 py-3
                    rounded-lg
                    mb-5
                ">
                    <?= htmlspecialchars($error) ?>
                </div>

            <?php endif; ?>

            <form method="POST" class="space-y-5">

                <div>

                    <label class="
                        block
                        text-sm
                        font-medium
                        text-center
                        mb-2
                    ">
                        Enter OTP
                    </label>

                    <input
                        type="text"
                        name="otp"
                        maxlength="6"
                        minlength="6"
                        pattern="[0-9]{6}"
                        inputmode="numeric"
                        autocomplete="one-time-code"
                        placeholder="000000"
                        required
                        class="
                            w-full
                            border
                            border-gray-300
                            rounded-lg
                            px-4
                            py-4
                            text-center
                            text-3xl
                            tracking-[0.5em]
                            focus:ring-2
                            focus:ring-blue-500
                            focus:outline-none
                        "
                    >

                </div>

                <button
                    type="submit"
                    class="
                        w-full
                        bg-blue-600
                        hover:bg-blue-700
                        text-white
                        font-semibold
                        py-3
                        rounded-lg
                        transition
                    "
                >
                    Verify OTP
                </button>

            </form>

            <div class="text-center mt-6">

                <p class="text-sm text-gray-500">
                    OTP expires after 5 minutes.
                </p>

                <a
                    href="login.php"
                    class="
                        inline-block
                        mt-3
                        text-blue-600
                        hover:underline
                    "
                >
                    Back to Login
                </a>

            </div>

        </div>

    </div>

</div>

</body>
</html>