# Practical 6: Rendering External JSON Data using Fetch API, Search & Filter

**Course:** Web Development Framework (WDF)  
**Project:** StudentHub Portal  
**CO Mapping:** CO3, CO4  

---

## 1. Problem Definition & Objectives
To consume external, decoupled JSON datasets (`events.json`, `students.json`, `faqs.json` with 15+ records each) using the modern JavaScript `Fetch API`. The application dynamically renders modular UI views, provides live instant search across multiple fields, category filtering, multi-criteria sorting, client-side pagination, dependent cascading dropdowns, and offline `localStorage` caching.

---

## 2. Key Questions & Technical Analysis

### Q1: How is JSON fetched, parsed, and rendered?
- Using asynchronous `async/await` and the `fetch(url)` promise-based API:
```javascript
const response = await fetch('data/events.json');
const data = await response.json();
```
- The parsed JavaScript array is mapped to HTML template literals (`Array.prototype.map()`) and injected into the DOM container via `innerHTML`.

### Q2: Which array methods are used for search, filter, sort, and pagination?
- **Filter & Search**: `Array.prototype.filter()` with `String.prototype.includes()` and `toLowerCase()`.
- **Sort**: `Array.prototype.sort()` using `localeCompare()` for string comparisons and numerical comparison for dates/GPAs.
- **Pagination**: `Array.prototype.slice(startIndex, endIndex)` where `startIndex = (currentPage - 1) * pageSize` and `endIndex = startIndex + pageSize`.
- **Rendering**: `Array.prototype.map().join("")`.

### Q3: How are loading and error states handled?
- **Loading State**: Displays a centered CSS animation spinner while the asynchronous network request is in progress.
- **Error State**: Caught by `try...catch(err)`. Displays a dedicated error banner with a dynamic "Retry Connection" button.
- **Offline Cache**: Automatically restores the last cached JSON from `localStorage.getItem()` if the network is disconnected.

### Q4: How is modularity maintained in JavaScript files?
- Separation of concerns: Data is isolated in external JSON files (`events.json`, `students.json`, `faqs.json`).
- Reusable UI rendering functions (`renderEvents()`, `renderStudents()`, `renderFaqs()`).
- Centralized filtering pipeline (`applyFilterAndSort()`) feeding directly into the view renderer (`renderView()`).

---

## 3. Files in Practical-6
- `data/events.json`: 16 detailed university event records.
- `data/students.json`: 16 student profile records with GPA and specialization.
- `data/faqs.json`: 16 categorized FAQ records.
- `index.html`: Responsive data portal interface with search, filters, and pagination.
- `style.css`: Data card styles, badges, responsive controls, and pagination indicators.
- `app.js`: Complete asynchronous Fetch API logic, search algorithms, and dependent dropdown handlers.
