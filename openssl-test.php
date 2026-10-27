<?php

echo "PHP Version: " . PHP_VERSION . "<br>";

if (extension_loaded("openssl")) {
    echo "OpenSSL: <strong>ENABLED</strong>";
} else {
    echo "OpenSSL: <strong>DISABLED</strong>";
}