
const token = localStorage.getItem('token');
if (!token) {

    // Redirect to login page if token is not found
    window.location.href = 'login.php';

}
