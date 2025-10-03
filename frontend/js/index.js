document.addEventListener("DOMContentLoaded", () => {
    const dropdown = document.querySelector(".dropdown");
    const dropdownMenu = dropdown.querySelector(".dropdown-menu");

    let hoverTimer;

    dropdown.addEventListener("mouseenter", () => {
        hoverTimer = setTimeout(() => {
            dropdownMenu.style.display = "block";
        }, 250); // 0.25s delay
    });

    dropdown.addEventListener("mouseleave", () => {
        clearTimeout(hoverTimer);
        dropdownMenu.style.display = "none";
    });
});
