// import '../../resources/js/app';
// import '../../resources/views/app-layout';

// alert('ini adalah halaman dashboard');

const listItems = document.querySelectorAll(".sidebar-list li");

listItems.forEach((item) => {
  item.addEventListener("click", () => {
    let isActive = item.classList.contains("active");

    listItems.forEach((el) => {
      el.classList.remove("active");
    });

    if (isActive) item.classList.remove("active");
    else item.classList.add("active");
  });
});

const toggleSidebar = document.querySelector(".toggle-sidebar");
const logo = document.querySelector(".logo-box");
const sidebar = document.querySelector(".sidebar");

toggleSidebar.addEventListener("click", () => {
  sidebar.classList.toggle("close");
});

logo.addEventListener("click", () => {
  sidebar.classList.toggle("close");
});

const inputQty = document.querySelector(".input-qty");
function limitInputLength(inputQty, maxLength) {
  if (inputQty.value.length > maxLength) {
    inputQty.value = inputQty.value.slice(0, maxLength);
  }
};

