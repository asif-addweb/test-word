
document.addEventListener('DOMContentLoaded', function () {
    const passwordField = document.querySelector('#password');
    const confirmPasswordField = document.querySelector('#confirm_password');
    const errorElement = document.querySelector('.error-message');

    function validatePassword() {
        const passwordValue = passwordField.value;
        const confirmPasswordValue = confirmPasswordField.value;

        if (passwordValue !== confirmPasswordValue && passwordValue !== '' && confirmPasswordValue !== '') {
            errorElement.textContent = 'Passwords do not match.';
            confirmPasswordField.setCustomValidity('Passwords do not match.');
        } else {
            errorElement.textContent = '';
            confirmPasswordField.setCustomValidity('');
        }
    }

    passwordField.addEventListener('input', validatePassword);
    confirmPasswordField.addEventListener('input', validatePassword);
});