# Practical 4: JavaScript DOM Manipulation, Event Handling & UI Interactivity

**Course:** Web Development Framework (WDF)  
**Project:** StudentHub Portal  
**CO Mapping:** CO3, CO4  

---

## 1. Problem Definition & Objectives
To enhance user experience and client-side interactivity by implementing dynamic UI components using modern Vanilla JavaScript (ES6+):
1. **Light / Dark Theme Switcher** with persistent state storage in `localStorage`.
2. **Responsive Hamburger Drawer** for mobile navigation.
3. **Collapsible FAQ Accordion** using event delegation and smooth height transitions.
4. **Image & Content Slider** with timer intervals, pause-on-hover, arrow controls, and dot indicators.
5. **Modal Popup Window** for event registrations with backdrop dismiss and `ESC` key listening.
6. **Toast Notification Banner System** supporting Success, Error, Info, and Warning types.

---

## 2. Key Questions & Technical Analysis

### Q1: How are DOM elements selected and modified?
- **Selection**: `document.getElementById()`, `document.querySelector()`, and `document.querySelectorAll()` are used for precise, query-based node access.
- **Modification**: `element.classList.add()`, `.remove()`, and `.toggle()` modify classes dynamically for CSS-driven transitions. `element.innerHTML` and `.textContent` inject sanitized text and icons.

### Q2: Are event listeners attached correctly?
- Event listeners are attached using `element.addEventListener('click', handler)`.
- Global keyboard events (`keydown` for `Escape` key) and mouse events (`mouseenter`/`mouseleave` for slider auto-play pause) are cleanly bound.

### Q3: Is `localStorage` used for remembering theme choice?
Yes. When the theme toggle is clicked:
```javascript
localStorage.setItem('studenthub_theme', 'dark'); // or 'light'
```
On page initialization, `localStorage.getItem('studenthub_theme')` is evaluated to apply the preferred theme before the UI renders, preventing theme flickering.

### Q4: Does interactivity improve usability without breaking accessibility?
Yes. All interactive elements have descriptive `aria-label`, visible focus states, and keyboard accessibility.

---

## 3. Files in Practical-4
- `index.html`: Interactive demo dashboard showcasing all 6 UI components.
- `style.css`: Modern CSS styling with CSS custom property theming and animations.
- `script.js`: Modular ES6+ JavaScript handling all DOM operations and events.
