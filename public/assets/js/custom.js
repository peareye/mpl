// Set height (in px), numbers only
const scrollRemoveClass = 25
const scrollAddClass = 100;
const headerScrollClass = "jsHeaderScroll";

const headerElement = document.querySelector("header.topHeader");
window.onscroll = function () {
    let top = document.documentElement.scrollTop;

    if (top <= scrollRemoveClass) {
        headerElement.classList.remove(headerScrollClass);
    }

    if (top >= scrollAddClass) {
        headerElement.classList.add(headerScrollClass);
    }
}