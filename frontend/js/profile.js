// Select relevant elements
const editProfileBtn = document.querySelector('.edit-profile');
const cancelBtn = document.querySelector('.cancel-btn');
const profileHeader = document.querySelector('.profile-header');

// Function to enter edit mode
function enterEditMode() {
    profileHeader.classList.add('edit-mode');
}

// Function to exit edit mode
function exitEditMode() {
    profileHeader.classList.remove('edit-mode');
}

// Event listeners
editProfileBtn.addEventListener('click', enterEditMode);
cancelBtn.addEventListener('click', exitEditMode);

