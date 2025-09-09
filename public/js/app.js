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

console.log("Toggle Sidebar Debug:");
console.log("toggleSidebar element:", toggleSidebar);
console.log("sidebar element:", sidebar);
console.log("logo element:", logo);

// Null check untuk mencegah error
if (toggleSidebar && sidebar) {
    console.log("Adding click event to toggle-sidebar");
    toggleSidebar.addEventListener("click", (e) => {
      console.log("Toggle sidebar clicked!");
      e.preventDefault();
      sidebar.classList.toggle("close");
      console.log("Sidebar classes after toggle:", sidebar.classList.toString());
    });
} else {
    console.log("toggleSidebar or sidebar not found!");
}

if (logo && sidebar) {
    console.log("Adding click event to logo");
    logo.addEventListener("click", (e) => {
      console.log("Logo clicked!");
      e.preventDefault();
      sidebar.classList.toggle("close");
      console.log("Sidebar classes after logo click:", sidebar.classList.toString());
    });
} else {
    console.log("logo or sidebar not found!");
}

const inputQty = document.querySelector(".input-qty");
function limitInputLength(inputQty, maxLength) {
  if (inputQty.value.length > maxLength) {
    inputQty.value = inputQty.value.slice(0, maxLength);
  }
};

