document.addEventListener("DOMContentLoaded", () => {
  const yearSpans = document.querySelectorAll(".current-year");
  const year = new Date().getFullYear();
  yearSpans.forEach((span) => (span.textContent = year));

  const toggles = document.querySelectorAll(".hamburger-btn");

  toggles.forEach((toggle) => {
    toggle.addEventListener("click", (e) => {
      e.stopPropagation();
      const targetId = toggle.getAttribute("data-target");
      const menu = document.getElementById(targetId);

      document.querySelectorAll(".dropdown-menu").forEach((dropdown) => {
        if (dropdown !== menu) dropdown.classList.remove("show");
      });

      if (menu) {
        menu.classList.toggle("show");
      }
    });
  });

  document.addEventListener("click", () => {
    document.querySelectorAll(".dropdown-menu").forEach((dropdown) => {
      dropdown.classList.remove("show");
    });
  });

  createToastContainer();

  bindFormToast("login-form", "Login successful.");
  bindFormToast("register-form", "Account created successfully.");
  bindFormToast("profile-form", "Profile updated successfully.");
  bindFormToast("add-product-form", "Product added successfully.");
  bindFormToast("request-form", "Request submitted successfully.");
  bindFormToast("search-form", null, true);

  bindActionMessages();
});

function createToastContainer() {
  if (!document.getElementById("toast-container")) {
    const container = document.createElement("div");
    container.id = "toast-container";
    document.body.appendChild(container);
  }
}

function showToast(message, type = "success") {
  const container = document.getElementById("toast-container");
  if (!container) return;

  const toast = document.createElement("div");
  toast.className = `toast-message toast-${type}`;

  const icon = document.createElement("span");
  icon.className = "toast-icon";
  icon.textContent = type === "success" ? "✓" : type === "warning" ? "!" : "✕";

  const text = document.createElement("span");
  text.className = "toast-text";
  text.textContent = message;

  toast.appendChild(icon);
  toast.appendChild(text);
  container.appendChild(toast);

  setTimeout(() => {
    toast.classList.add("show");
  }, 50);

  setTimeout(() => {
    toast.classList.remove("show");
    setTimeout(() => {
      toast.remove();
    }, 300);
  }, 2600);
}

function bindFormToast(formId, message, isSearchForm = false) {
  const form = document.getElementById(formId);
  if (!form) return;

  form.addEventListener("submit", (e) => {
    e.preventDefault();

    if (isSearchForm) {
      const input = document.getElementById("search-name");
      const value = input ? input.value.trim().toLowerCase() : "";

      if (value === "" || value === "tomatoes" || value === "dates") {
        showToast("Search completed successfully.", "success");
      } else {
        showToast("No matching products were found.", "warning");
      }
      return;
    }

    showToast(message, "success");
  });
}

function bindActionMessages() {
  const actionMap = [
    { selector: ".delete-product-btn", message: "Product deleted successfully.", type: "success" },
    { selector: ".edit-product-btn", message: "Product updated successfully.", type: "success" },
    { selector: ".approve-request-btn", message: "Request approved successfully.", type: "success" },
    { selector: ".reject-request-btn", message: "Request rejected successfully.", type: "warning" },
    { selector: ".block-user-btn", message: "User blocked successfully.", type: "warning" },
    { selector: ".unblock-user-btn", message: "User unblocked successfully.", type: "success" },
    { selector: ".block-product-btn", message: "Product blocked successfully.", type: "warning" },
    { selector: ".unblock-product-btn", message: "Product unblocked successfully.", type: "success" },
    { selector: ".logout-btn", message: "Logged out successfully.", type: "success" }
  ];

  actionMap.forEach((item) => {
    const elements = document.querySelectorAll(item.selector);

    elements.forEach((element) => {
      element.addEventListener("click", (e) => {
        e.preventDefault();
        showToast(item.message, item.type);
      });
    });
  });
}