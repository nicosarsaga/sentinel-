<?php
session_start();

require_once __DIR__ . "/includes/functions.php";
requireLogin();

$username = $_SESSION["username"] ?? "User";
$firstname = $_SESSION["firstname"] ?? "User";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SENTINEL | Password Security Analyzer</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/sentinel.css">
</head>
<body>

<div class="app">

    <aside class="sidebar">
        <div class="brand">
            <div class="brand-icon">S</div>
            <div>
                <h1>SENTINEL</h1>
                <span>SECURITY ANALYZER</span>
            </div>
        </div>

        <nav>
            <button class="nav-item active" data-page="dashboard">◉ <span>Dashboard</span></button>
            <button class="nav-item" data-page="analyzer">⌕ <span>Password Analyzer</span></button>
            <button class="nav-item" data-page="report">▤ <span>Security Report</span></button>
            <button class="nav-item" data-page="guide">? <span>Security Guide</span></button>
        </nav>

        <div class="sidebar-bottom">
            <div class="system-status">
                <span class="status-dot"></span>
                <div>
                    <strong>AUTHENTICATED</strong>
                    <small><?= e($username) ?></small>
                </div>
            </div>
            <a class="logout-link" href="logout.php">LOG OUT</a>
            <div class="version">SENTINEL v1.0.0</div>
        </div>
    </aside>

    <main class="main">

        <header class="topbar">
            <div>
                <span class="eyebrow">INFORMATION ASSURANCE & SECURITY</span>
                <h2 id="pageTitle">Security Dashboard</h2>
            </div>

            <div class="top-status">
                <div class="live"><span></span>SYSTEM ONLINE</div>
                <div class="clock" id="clock">00:00:00</div>
            </div>
        </header>

        <!-- LANDING DASHBOARD -->
        <section class="page active" id="dashboard">
            <div class="hero">
                <div>
                    <span class="eyebrow">WELCOME, <?= e(strtoupper($firstname)) ?></span>
                    <h3>
                        Analyze password security
                        <span>before attackers do.</span>
                    </h3>
                    <p>
                        SENTINEL performs multi-layer password analysis
                        including length, character diversity, predictable
                        patterns, repetition, keyboard sequences and
                        estimated entropy.
                    </p>
                    <button class="primary-btn" data-open="analyzer">
                        START ANALYSIS <span>→</span>
                    </button>
                </div>

                <div class="hero-decoration">
                    <div class="radar"></div>
                    <div class="radar-center">S</div>
                </div>
            </div>

            <div class="section-title"><span>01</span> SECURITY MODULES</div>

            <div class="module-grid">
                <div class="module-card">
                    <div class="module-icon cyan">⌕</div>
                    <h4>Pattern Analysis</h4>
                    <p>Detects sequences, repetition, keyboard patterns and predictable structures.</p>
                    <span class="module-status">READY</span>
                </div>

                <div class="module-card">
                    <div class="module-icon purple">◈</div>
                    <h4>Entropy Analysis</h4>
                    <p>Estimates theoretical character-space complexity of the password.</p>
                    <span class="module-status">READY</span>
                </div>

                <div class="module-card">
                    <div class="module-icon green">✓</div>
                    <h4>Privacy Engine</h4>
                    <p>Password analysis happens locally inside your browser.</p>
                    <span class="module-status">ACTIVE</span>
                </div>

                <div class="module-card">
                    <div class="module-icon orange">⚠</div>
                    <h4>Risk Detection</h4>
                    <p>Identifies common password structures and potential weaknesses.</p>
                    <span class="module-status">READY</span>
                </div>
            </div>

            <div class="privacy-card">
                <div class="privacy-icon">🔒</div>
                <div>
                    <strong>Privacy-First Architecture</strong>
                    <p>The password under analysis is not submitted to PHP/MySQL by this dashboard.</p>
                </div>
                <span class="secure-badge">LOCAL</span>
            </div>
        </section>

        <!-- ANALYZER -->
        <section class="page" id="analyzer">
            <div class="section-heading">
                <div>
                    <span class="eyebrow">MODULE 01</span>
                    <h3>Password Security Analyzer</h3>
                    <p>Perform a multi-layer assessment of a password.</p>
                </div>
                <div class="privacy-mini">🔒 LOCAL PROCESSING</div>
            </div>

            <div class="analyzer-layout">
                <div class="panel input-panel">
                    <div class="panel-header">
                        <div><span class="panel-number">01</span><h4>Password Input</h4></div>
                        <span class="local-tag">LOCAL</span>
                    </div>

                    <div class="password-input">
                        <input type="password" id="password" placeholder="Enter password for analysis..." autocomplete="off">
                        <button id="togglePassword" type="button">◉</button>
                    </div>

                    <div class="input-meta">
                        <span id="charCount">0 characters</span>
                        <span>Not stored</span>
                    </div>

                    <div class="requirements">
                        <div class="requirement" id="reqLength"><span>✓</span>12+ characters</div>
                        <div class="requirement" id="reqUpper"><span>✓</span>Uppercase</div>
                        <div class="requirement" id="reqLower"><span>✓</span>Lowercase</div>
                        <div class="requirement" id="reqNumber"><span>✓</span>Numbers</div>
                        <div class="requirement" id="reqSymbol"><span>✓</span>Symbols</div>
                        <div class="requirement" id="reqUnique"><span>✓</span>Character diversity</div>
                    </div>

                    <button class="secondary-btn" id="clearPassword" type="button">CLEAR PASSWORD</button>
                </div>

                <div class="panel score-panel">
                    <div class="score-ring" id="scoreRing">
                        <div class="score-inner">
                            <strong id="score">0</strong>
                            <span>/ 100</span>
                        </div>
                    </div>
                    <div class="score-label" id="strengthLabel">WAITING FOR INPUT</div>
                    <p id="scoreDescription">Enter a password to begin the assessment.</p>
                </div>
            </div>

            <div class="analysis-grid">
                <div class="panel">
                    <div class="panel-header"><div><span class="panel-number">02</span><h4>Structural Analysis</h4></div></div>
                    <div class="metrics">
                        <div class="metric"><span>Length</span><strong id="metricLength">—</strong></div>
                        <div class="metric"><span>Uppercase</span><strong id="metricUpper">—</strong></div>
                        <div class="metric"><span>Lowercase</span><strong id="metricLower">—</strong></div>
                        <div class="metric"><span>Numbers</span><strong id="metricNumber">—</strong></div>
                        <div class="metric"><span>Symbols</span><strong id="metricSymbol">—</strong></div>
                        <div class="metric"><span>Unique Ratio</span><strong id="metricUnique">—</strong></div>
                    </div>
                </div>

                <div class="panel">
                    <div class="panel-header"><div><span class="panel-number">03</span><h4>Threat Analysis</h4></div></div>
                    <div class="threat-list">
                        <div class="threat"><span>Sequential Pattern</span><strong id="sequenceStatus">—</strong></div>
                        <div class="threat"><span>Repetition</span><strong id="repeatStatus">—</strong></div>
                        <div class="threat"><span>Keyboard Pattern</span><strong id="keyboardStatus">—</strong></div>
                        <div class="threat"><span>Common Password</span><strong id="commonStatus">—</strong></div>
                        <div class="threat"><span>Year / Date Pattern</span><strong id="dateStatus">—</strong></div>
                    </div>
                </div>

                <div class="panel entropy-panel">
                    <div class="panel-header"><div><span class="panel-number">04</span><h4>Entropy Estimation</h4></div></div>
                    <div class="entropy-value"><strong id="entropy">0</strong><span>bits</span></div>
                    <div class="entropy-bar"><div id="entropyFill"></div></div>
                    <div class="entropy-footer">
                        <span>Estimated character-space complexity</span>
                        <span id="poolSize">Pool: 0</span>
                    </div>
                </div>
            </div>

            <div class="panel recommendations">
                <div class="panel-header"><div><span class="panel-number">05</span><h4>Security Recommendations</h4></div></div>
                <div id="recommendationsList">
                    <div class="recommendation neutral"><span>i</span>Enter a password to generate recommendations.</div>
                </div>
            </div>
        </section>

        <!-- GENERATOR -->
        <section class="page" id="generator">
            <div class="section-heading">
                <div>
                    <span class="eyebrow">MODULE 02</span>
                    <h3>Secure Password Generator</h3>
                    <p>Generate passwords locally using the browser's secure random API when available.</p>
                </div>
            </div>

            <div class="generator-layout">
                <div class="panel generator-settings">
                    <h4>Generator Configuration</h4>

                    <label>Password Length <strong id="lengthValue">24</strong></label>
                    <input type="range" id="passwordLength" min="8" max="64" value="24">

                    <div class="generator-options">
                        <label><input type="checkbox" id="genUpper" checked> Uppercase</label>
                        <label><input type="checkbox" id="genLower" checked> Lowercase</label>
                        <label><input type="checkbox" id="genNumbers" checked> Numbers</label>
                        <label><input type="checkbox" id="genSymbols" checked> Symbols</label>
                    </div>

                    <button class="primary-btn" id="generatePassword" type="button">GENERATE PASSWORD</button>
                </div>

                <div class="panel generated-panel">
                    <span class="eyebrow">GENERATED PASSWORD</span>
                    <div class="generated-password" id="generatedPassword">Click generate</div>
                    <button class="secondary-btn" id="copyGenerated" type="button">COPY TO CLIPBOARD</button>
                    <div class="generator-note">Generated locally; the generated password is not written to the SENTINEL database.</div>
                </div>
            </div>
        </section>

        <!-- REPORT -->
        <section class="page" id="report">
            <div class="section-heading">
                <div>
                    <span class="eyebrow">MODULE 03</span>
                    <h3>Security Assessment Report</h3>
                    <p>Current in-browser assessment summary.</p>
                </div>
                <button class="primary-btn" id="printReport" type="button">PRINT REPORT</button>
            </div>

            <div class="report-sheet">
                <div class="report-header">
                    <div>
                        <strong>SENTINEL</strong>
                        <span>ADVANCED PASSWORD SECURITY ANALYZER</span>
                    </div>
                    <div>
                        <span>ASSESSMENT</span>
                        <strong id="reportDate">—</strong>
                    </div>
                </div>

                <div class="report-score">
                    <span>OVERALL SECURITY SCORE</span>
                    <strong id="reportScore">0 / 100</strong>
                    <b id="reportStrength">WAITING</b>
                </div>

                <div class="report-grid">
                    <div><span>Password Length</span><strong id="reportLength">—</strong></div>
                    <div><span>Estimated Entropy</span><strong id="reportEntropy">—</strong></div>
                    <div><span>Pattern Risk</span><strong id="reportRisk">—</strong></div>
                    <div><span>Processing</span><strong>LOCAL</strong></div>
                </div>

                <div class="report-footer">SENTINEL v1.0 • INFORMATION ASSURANCE PROJECT</div>
            </div>
        </section>

        <!-- GUIDE -->
        <section class="page" id="guide">
            <div class="section-heading">
                <div>
                    <span class="eyebrow">REFERENCE</span>
                    <h3>Security Guide</h3>
                    <p>Security concepts demonstrated by SENTINEL.</p>
                </div>
            </div>

            <div class="guide-grid">
                <article class="guide-card"><span>01</span><h4>Brute Force Attack</h4><p>An attacker systematically attempts possible passwords until a valid credential is found.</p></article>
                <article class="guide-card"><span>02</span><h4>Dictionary Attack</h4><p>An attacker uses lists of common words, passwords and predictable combinations.</p></article>
                <article class="guide-card"><span>03</span><h4>Credential Stuffing</h4><p>Previously leaked credential combinations are tested against other services.</p></article>
                <article class="guide-card"><span>04</span><h4>Password Hashing</h4><p>Passwords should be stored using dedicated password-hashing algorithms, never as plaintext.</p></article>
                <article class="guide-card"><span>05</span><h4>Multi-Factor Authentication</h4><p>MFA adds another authentication factor beyond the password.</p></article>
                <article class="guide-card"><span>06</span><h4>Password Manager</h4><p>Password managers can generate and store unique credentials for different services.</p></article>
            </div>
        </section>

    </main>
</div>

<script src="assets/sentinel.js"></script>
</body>
</html>
