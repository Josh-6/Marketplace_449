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

// Section 2 & 3: Tab Switching Logic
document.addEventListener("DOMContentLoaded", () => {
    const tabs = document.querySelectorAll(".tab-button");
    const tabContents = document.querySelectorAll(".tab-content");

    tabs.forEach(tab => {
        tab.addEventListener("click", () => {
            const target = tab.dataset.tab; // item-history, order-history, or account-settings

            // Remove 'active' from all tabs and tab contents
            tabs.forEach(t => t.classList.remove("active"));
            tabContents.forEach(tc => tc.classList.remove("active"));

            tab.classList.add("active");

            // Show tab content
            const activeContent = document.getElementById(target);
            if (activeContent) {
                activeContent.classList.add("active");
            }

            console.log(`Switched to tab: ${target}`);
        });
    });
});


