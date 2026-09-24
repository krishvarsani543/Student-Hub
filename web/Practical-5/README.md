# Practical 5: Registration Form with Frontend Validation & User-Friendly Error Handling

**Course:** Web Development Framework (WDF)  
**Project:** StudentHub Portal  
**CO Mapping:** CO1, CO3  

---

## 1. Problem Definition & Scope
To build an accessible, validated, and user-friendly student registration form using HTML5 input types, Regular Expressions (RegEx), real-time field error feedback, dynamic password strength meters, and custom Canvas-rendered CAPTCHA.

### Covered Input Fields:
- **Full Name**: Letters and whitespace only (`/^[A-Za-z\s]{3,50}$/`).
- **Email**: Strict standard email validation pattern.
- **Mobile Number**: 10-digit Indian phone starting with `6-9` (`/^[6-9]\d{9}$/`).
- **Date of Birth**: Age bounds check (15 - 65 years).
- **Degree Course & Academic Year**: Required selections from university curriculum.
- **Gender**: Accessible radio button group with validation.
- **Password & Confirm Password**: Strict complexity (`min 8 chars, 1 uppercase, 1 lowercase, 1 number, 1 special char`) + match verification.
- **Password Strength Meter**: Dynamic visual score indicator (Weak, Medium, Strong).
- **Residential Address**: Multi-line textarea validation (min 10 characters).
- **Canvas CAPTCHA**: Procedurally drawn alphanumeric security challenge on HTML5 `<canvas>`.
- **Terms Acceptance**: Mandatory policy consent checkbox.

---

## 2. Key Questions & Technical Analysis

### Q1: Are correct input types and attributes used?
Yes. Uses semantic types: `type="text"`, `type="email"`, `type="tel"`, `type="date"`, `type="password"`, `type="radio"`, `type="checkbox"`, with `required`, `maxlength`, `autocomplete`, and `novalidate` on the form to allow custom JS error styling.

### Q2: Are validation errors displayed near the relevant fields?
Yes. Each field group contains an inline `<span class="error-msg" id="...Error">` directly below the input element. Successful inputs turn green with `.success`, and invalid inputs highlight red with `.error`.

### Q3: Is password strength checked?
Yes. An input event listener scores the password across length, casing, numbers, and special characters, updating the visual width, color, and label in real time.

### Q4: Is the form accessible using keyboard and screen-reader-friendly labels?
Yes. Every input has a paired `<label for="...">`, visible focus styling, and explicit error message containers.

---

## 3. Files in Practical-5
- `index.html`: Complete registration form structure with Canvas CAPTCHA.
- `style.css`: Responsive CSS form layout with feedback indicators.
- `validation.js`: Modular validation routines, regex matching, and canvas drawing.
