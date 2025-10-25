// Section 1: Edit mode, will later need to add the ability to
// change profile pic and save it
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

// Section 2: Tab Switching Logic
document.addEventListener("DOMContentLoaded", () => {
    const tabs = document.querySelectorAll(".tab-button");

    tabs.forEach(tab => {
        tab.addEventListener("click", () => {
            // Remove active class from all tabs
            tabs.forEach(t => t.classList.remove("active"));
            // Add active class to clicked tab
            tab.classList.add("active");
            //console.log("Switchin actives");
            // Future: Display corresponding content in Section 3
            // Example:
            // showSection(tab.dataset.tab);
        });
    });
});
