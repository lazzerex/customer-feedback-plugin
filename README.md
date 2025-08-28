# 📝 Customer Feedback - WordPress Plugin

[![WordPress](https://img.shields.io/badge/WordPress-6.5+-blue.svg)](https://wordpress.org/) 
[![PHP](https://img.shields.io/badge/PHP-7.4%2B-8892BF.svg?logo=php&logoColor=white)](https://www.php.net/) 
[![License: MIT](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE) 
[![Contributions Welcome](https://img.shields.io/badge/contributions-welcome-brightgreen.svg?style=flat)](../../issues)

A customizable **Customer Feedback plugin** for WordPress that allows site owners to collect, manage, and analyze customer feedback with star ratings, detailed questions, and exportable reports. The plugin provides a sleek frontend feedback form and a powerful admin panel with validation, sorting, pagination, and Excel export.

---

## 🚀 Features

### 🔹 Frontend
- ⭐ Star rating system with hover and click interactions
- 😀 Emotion-based overall rating (e.g., 😍 😐 😡)
- ✨ Floating labels for input fields (name, phone, notes, etc.)
- ✅ Real-time validation with custom error messages
- 📱 Phone number auto-formatting (Vietnamese-friendly)
- 🎨 Responsive and modern UI with custom typography (Averta font)
- ⚡ AJAX-powered form submission with loading states & duplicate prevention

### 🔹 Admin Dashboard
- Manage feedback questions and reviews
- Table sorting for quick analysis
- Form validation for question management
- Custom delete confirmation dialogs
- Success/error notices auto-dismiss after 5s
- Review detail pages with star displays
- Export feedback to Excel for offline reporting
- Pagination and search functionality

### 🔹 Styling
- **Admin styles** optimized for WordPress UI consistency
- **Frontend styles** designed for usability and responsiveness
- Floating labels and dotted border inputs for a clean, modern form

---

## ⚙️ Installation

1. Download or clone this repository:
   ```bash
   git clone https://github.com/yourusername/customer-feedback.git
   ```

2. Upload the `customer-feedback` folder to your WordPress `wp-content/plugins/` directory.

3. Activate **Customer Feedback** from the WordPress admin dashboard.

---

## 🛠️ Usage

### Embedding Feedback Form
Add the shortcode in any page or post:

```php
[customer_feedback_form]
```

### Exporting Feedback
Navigate to **Customer Feedback → Export** and click **Export to Excel**.

---

## 📸 Screenshots 

<img width="982" height="624" alt="image" src="https://github.com/user-attachments/assets/b8d37896-703b-469f-bbe2-732653757572" />

<img width="801" height="887" alt="image" src="https://github.com/user-attachments/assets/246e3950-923c-4470-8022-14bbfc450722" />

---

## 🤝 Contributing

Pull requests are welcome!

If you'd like to contribute:
1. Fork the repo
2. Create a feature branch
3. Submit a PR

---

## 📜 License

This project is licensed under the MIT License – see the [LICENSE](LICENSE) file for details.
