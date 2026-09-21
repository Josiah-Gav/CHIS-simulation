<?php
/** @var array $config */
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($config['app_name'] ?? 'CHIS Simulation') ?></title>
    <link rel="stylesheet" href="/assets/style.css">
</head>
<body>
<div class="shell">
    <nav class="sidebar">
        <div class="brand">CHIS <span>Simulation</span></div>
        <a href="/">Dashboard</a>
        <p class="sidebar-note">
            Standalone stand-in for the Comprehensive Health Information System (CHIS).
            Not a real health information system — built to demonstrate interoperability
            with CLSU Telemedicine.
        </p>
    </nav>
    <main class="content">
