<?php
/**
 * Practical 14: Dynamic Role-Based Menu Generator (Key Question #3)
 * Loads menu items dynamically based on the current user's authenticated role.
 */

function renderDynamicMenu(string $role, string $activePage = "dashboard"): void
{
    $menuItems = [];

    if ($role === "admin") {
        $menuItems = [
            [
                "label"  => "Dashboard Overview",
                "icon"   => "fa-gauge-high",
                "url"    => "index.php",
                "id"     => "dashboard",
                "badge"  => "Live",
                "bColor" => "#10b981"
            ],
            [
                "label"  => "Student Moderation",
                "icon"   => "fa-user-graduate",
                "url"    => "#studentsSection",
                "id"     => "students",
                "badge"  => null
            ],
            [
                "label"  => "Event Analytics",
                "icon"   => "fa-chart-pie",
                "url"    => "#chartSection",
                "id"     => "analytics",
                "badge"  => "Chart.js",
                "bColor" => "#8b5cf6"
            ],
            [
                "label"  => "Recent Registrations",
                "icon"   => "fa-clipboard-list",
                "url"    => "#registrationsSection",
                "id"     => "registrations",
                "badge"  => null
            ],
            [
                "label"  => "Security Audit Trail",
                "icon"   => "fa-clock-rotate-left",
                "url"    => "#auditSection",
                "id"     => "audit",
                "badge"  => "Protected",
                "bColor" => "#ef4444"
            ]
        ];
    } else {
        // Student Role Menu
        $menuItems = [
            [
                "label"  => "Student Portal",
                "icon"   => "fa-home",
                "url"    => "student_view.php",
                "id"     => "student_home",
                "badge"  => "Student",
                "bColor" => "#3b82f6"
            ],
            [
                "label"  => "Browse Events",
                "icon"   => "fa-calendar-days",
                "url"    => "../Practical-2/events.html",
                "id"     => "events",
                "badge"  => null
            ],
            [
                "label"  => "My Registrations",
                "icon"   => "fa-ticket",
                "url"    => "#myRegistrations",
                "id"     => "my_reg",
                "badge"  => null
            ],
            [
                "label"  => "Profile Settings",
                "icon"   => "fa-id-card",
                "url"    => "../Practical-2/profile.html",
                "id"     => "profile",
                "badge"  => null
            ],
            [
                "label"  => "Test Admin Dashboard (RBAC)",
                "icon"   => "fa-shield-halved",
                "url"    => "index.php",
                "id"     => "test_admin",
                "badge"  => "Blocked",
                "bColor" => "#ef4444"
            ]
        ];
    }

    echo '<ul class="dynamic-nav-list">';
    foreach ($menuItems as $item) {
        $isActive = ($item["id"] === $activePage) ? "active" : "";
        echo '<li class="nav-item ' . $isActive . '">';
        echo '<a href="' . htmlspecialchars($item["url"], ENT_QUOTES, "UTF-8") . '">';
        echo '<i class="fa-solid ' . htmlspecialchars($item["icon"], ENT_QUOTES, "UTF-8") . '"></i>';
        echo '<span>' . htmlspecialchars($item["label"], ENT_QUOTES, "UTF-8") . '</span>';
        if (!empty($item["badge"])) {
            $style = !empty($item["bColor"]) ? 'style="background:' . $item["bColor"] . ';"' : '';
            echo '<span class="nav-badge" ' . $style . '>' . htmlspecialchars($item["badge"], ENT_QUOTES, "UTF-8") . '</span>';
        }
        echo '</a>';
        echo '</li>';
    }
    echo '</ul>';
}
