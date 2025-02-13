<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles/styles.css">
    <title>MemoryMap</title>
    <style>

        .container {
            display: flex;
            flex-direction: column; /* Stack elements vertically */
            max-width: 960px;
            margin: 20px auto;
            background-color: white;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        
        .main-content {
            padding: 20px;
            display: flex;
            flex-wrap: wrap; /* Allow wrapping on smaller screens */
        }
        .left-panel {
            flex: 1; /* Takes up available space */
            margin-right: 20px;
        }
        .user-info {
            border: 1px solid #ddd;
            padding: 10px;
            margin-bottom: 20px;
        }
        .reminders {
            flex: 2; /* Takes up more space than left panel */
            border: 1px solid #ddd;
            padding: 10px;
        }
        .schedule {
            margin-top: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 8px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .popup {
            display: none;
            position: absolute;
            top: 60px; /* Adjust as needed */
            right: 20px; /* Adjust as needed */
            background-color: white;
            border: 1px solid #ddd;
            padding: 10px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
            z-index: 1; /* Ensure it's on top */
        }
        .popup a {
            display: block;
            padding: 5px 0;
            color: #333;
            text-decoration: none;
        }
        .popup a:hover {
            background-color: #f0f0f0;
        }
        .user-icon {
            cursor: pointer; /* Make it clickable */
        }
        @media (max-width: 768px) {.main-content {
            flex-direction: column; /* Stack panels vertically on smaller screens */
        }
        
        .left-panel,.reminders {
            margin-right: 0; /* Remove margin when stacked */
            margin-bottom: 20px;
    }
}

    </style>
</head>
<body>
    <script>
        // Check if token exists in local storage
        const token = localStorage.getItem('token');
        if (!token) {
            // Redirect to login page if token is not found
            
            window.location.href = 'login.php';
        }
    </script>
    <?php
    include 'components/navigation.php';
    ?>
    <script>
        document.querySelector('a[href="patient.php"]').classList.add('active');
    </script>
    <div class="container">
        <div class="main-content">
            <div class="left-panel">
                <div class="user-info">
                    <img src="placeholder.png" alt="User Icon" class="user-icon" width="50"> <span id="user-name">Mark Deaniel Aquino</span>
                    <p>Brief info about Patient</p>
                </div>
                <div class="options">
                    <p>Calendar</p>
                    <p>Patient Logs</p>
                </div>
                <div class="reminders">
                    <h3>Patient Reminders</h3>
                    <p>Sunday, January xx, 20xx</p>
                    <ul id="reminders-list">
                        <!-- Reminders will be populated here -->
                    </ul>
                </div>
            </div>

            <div class="reminders">
                <h3>Scheduled Reminders</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Sun</th>
                            <th>Mon</th>
                            <th>Tue</th>
                            <th>Wed</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>6:00 AM: Medication, Prep Breakfast</td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                        <tr>
                            <td>7:00 AM: Breakfast</td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                        <tr>
                            <td>8:00 AM: Fix Room</td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                        <tr>
                            <td>11:00 AM: Prep Lunch</td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                        <tr>
                            <td>12:00 PM: Lunch</td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                    </tbody>
                </table>
                <div class="schedule">
                    <button>Add Schedule</button>
                </div>
            </div>
        </div>

        <div class="popup" id="user-popup">
            <img src="placeholder.png" alt="User Avatar" width="80">
            <span id="popup-user-name">Daniel Joshua Cariaso</span>
            <p>danjoshua@email.com</p>
            <a href="#">Edit Information</a>
            <a href="#">Change Password</a>
            <label><input type="checkbox"> Dark Mode</label>
            <a href="#">Privacy and Policy</a>
            <a href="#">Logout</a>
        </div>


    </div>

    <script>
        const userIcon = document.querySelector('.user-icon');
        const userName = document.getElementById('user-name');
        const popup = document.getElementById('user-popup');
        const popupUserName = document.getElementById('popup-user-name');

        userIcon.addEventListener('click', () => {
            popup.style.display = 'block';
            popupUserName.textContent = userName.textContent; // Copy name to popup
        });

        // Close the popup if clicked outside
        window.addEventListener('click', (event) => {
            if (event.target == popup) {
                popup.style.display = 'none';
            }
        });

        // Fetch reminders based on caregiver_id
        document.addEventListener('DOMContentLoaded', () => {
            const caregiverId = localStorage.getItem('caregiver_id');
            if (caregiverId) {
                fetch('../api/fetch_reminders.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ caregiver_id: caregiverId })
                })
                .then(response => response.json())
                .then(data => {
                    const remindersList = document.getElementById('reminders-list');
                    if (data.status === 'Success') {
                        data.reminders.forEach(reminder => {
                            const li = document.createElement('li');
                            li.textContent = `${reminder.time}: ${reminder.description}`;
                            remindersList.appendChild(li);
                        });
                    } else {
                        remindersList.textContent = 'No reminders found.';
                    }
                })
                .catch(error => {
                    console.error('Error fetching reminders:', error);
                });
            }
        });
    </script>

</body>
</html>