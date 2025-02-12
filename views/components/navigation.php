<?php
// navigation.php?>

<nav>
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;700&display=swap" rel="stylesheet">
    <div class="logo">MemoryMap</div>
    <ul>
        <li><a href="map.php">Map</a></li>
        <li><a href="dashboard.php">Dashboard</a></li>
        <li><a href="patient.php">Patient</a></li>
        <li class="settings">
            <a href="#" id="settings-toggle"><img src="../res/settings.png" alt="Settings"></a>
            <ul class="dropdown" id="settings-dropdown">
                <li><a href="profile.php">Profile</a></li>
                <li><a href="edit-info.php">Edit Information</a></li>
                <li><a href="change-password.php">Change Password</a></li>
                <li><a href="#" id="dark-mode-toggle">Dark Mode</a></li>
                <li><a href="privacy-policy.php">Privacy Policy</a></li>
                <li><a href="logout.php">Logout</a></li>
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
</script>

<style>
    .settings {
        position: relative;
    }
    .settings .dropdown {
        display: none;
        position: absolute;
        top: 100%;
        right: 0;
        background: white;
        list-style: none;
        padding: 0;
        margin: 0;
        box-shadow: 0 8px 16px rgba(0,0,0,0.2);
    }
    .settings .dropdown.show {
        display: block;
    }
</style>