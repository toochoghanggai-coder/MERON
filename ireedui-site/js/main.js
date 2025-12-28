// Mobile nav toggle
const navToggle = document.getElementById("navToggle");
const nav = document.getElementById("nav");

if (navToggle && nav) {
  navToggle.addEventListener("click", () => {
    const isOpen = nav.classList.toggle("is-open");
    navToggle.setAttribute("aria-expanded", String(isOpen));
  });

  // Close menu when clicking a link (mobile)
  nav.querySelectorAll("a").forEach((a) => {
    a.addEventListener("click", () => {
      nav.classList.remove("is-open");
      navToggle.setAttribute("aria-expanded", "false");
    });
  });

  // Close menu when clicking outside
  document.addEventListener("click", (e) => {
    const target = e.target;
    if (!nav.contains(target) && !navToggle.contains(target)) {
      nav.classList.remove("is-open");
      navToggle.setAttribute("aria-expanded", "false");
    }
  });
}

// Footer year
const year = document.getElementById("year");
if (year) year.textContent = String(new Date().getFullYear());

// Contact form demo (no actual submission)
const form = document.getElementById("contactForm");
const note = document.getElementById("formNote");

if (form && note) {
  form.addEventListener("submit", (e) => {
    e.preventDefault();

    const data = new FormData(form);
    const name = (data.get("name") || "").toString().trim();
    const email = (data.get("email") || "").toString().trim();
    const message = (data.get("message") || "").toString().trim();

    // Minimal validation (HTML required handles most)
    if (!name || !email || !message) {
      note.textContent = "未入力の項目があります。ご確認ください。";
      return;
    }

    note.textContent =
      "（デモ）送信内容を受け取りました。実運用ではメール送信やフォームサービス連携に置き換えます。";
    form.reset();
  });
}
