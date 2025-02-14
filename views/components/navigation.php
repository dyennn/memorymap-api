<?php
// navigation.php?>

<nav>
<link rel="stylesheet" href="../styles/styles.css">
    <div class="logo">MemoryMap</div>
    <div class="burger" id="burger">
        <div></div>
        <div></div>
        <div></div>
    </div>
    <ul id="nav-links">
        <li><a href="map.php">Map</a></li>
        <li><a href="dashboard.php">Dashboard</a></li>
        <li><a href="patient.php">Patient</a></li>
        <li class="settings">
            <a href="#" id="settings-toggle"><img src="../res/settings.png" alt="Settings"></a>
            <ul class="dropdown" id="settings-dropdown">
                <li><a href="profile.php">Mark Deaniel Aquino</a></li>
                <li><a href="edit-info.php">Edit Information</a></li>
                <li><a href="change-password.php">Change Password</a></li>
                <li><a href="#" id="dark-mode-toggle">Dark Mode</a></li>
                <li><a href="privacy-policy.php">Privacy Policy</a></li>
                <li><a href="../api/logout.php">Logout</a></li>
            </ul>
        </li>
    </ul>
</nav>

<script>
    document.getElementById('dark-mode-toggle').addEventListener('click', function() {
        document.body.classList.toggle('dark-mode');
    });

    document.getElementById('settings-toggle').addEventListener('click', function(event) {
        event.preventDefault();
        document.getElementById('settings-dropdown').classList.toggle('show');
    });

    window.addEventListener('click', function(event) {
        if (!event.target.matches('#settings-toggle') && !event.target.closest('.settings')) {
            document.getElementById('settings-dropdown').classList.remove('show');
        }
    });

    document.getElementById('burger').addEventListener('click', function() {
        document.getElementById('nav-links').classList.toggle('show');
    });
</script>