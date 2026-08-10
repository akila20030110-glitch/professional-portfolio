document.addEventListener("DOMContentLoaded", () => {
    const hoverTargets = document.querySelectorAll(
        ".action-card, .stat-card, .item-card, .mini-card, .recent-row, .claim-row, .notification-card, .admin-claim-card"
    );

    const tiltTargets = document.querySelectorAll(
        ".action-card, .stat-card, .item-card, .mini-card"
    );

    const canHover = window.matchMedia("(hover: hover) and (pointer: fine)").matches;
    const reducedMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;

    if (!canHover || reducedMotion) {
        return;
    }

    hoverTargets.forEach((card) => {
        card.addEventListener("mousemove", (event) => {
            const rect = card.getBoundingClientRect();
            const x = event.clientX - rect.left;
            const y = event.clientY - rect.top;

            const mx = (x / rect.width) * 100;
            const my = (y / rect.height) * 100;

            card.style.setProperty("--mx", `${mx}%`);
            card.style.setProperty("--my", `${my}%`);
        });

        card.addEventListener("mouseleave", () => {
            card.style.setProperty("--mx", "50%");
            card.style.setProperty("--my", "50%");
        });
    });

    tiltTargets.forEach((card) => {
        card.addEventListener("mouseenter", () => {
            card.classList.add("mouse-active");
        });

        card.addEventListener("mousemove", (event) => {
            const rect = card.getBoundingClientRect();

            const x = event.clientX - rect.left;
            const y = event.clientY - rect.top;

            const px = x / rect.width;
            const py = y / rect.height;

            const rotateY = (px - 0.5) * 7;
            const rotateX = (0.5 - py) * 7;

            card.style.setProperty("--rx", `${rotateX.toFixed(2)}deg`);
            card.style.setProperty("--ry", `${rotateY.toFixed(2)}deg`);
        });

        card.addEventListener("mouseleave", () => {
            card.classList.remove("mouse-active");
            card.style.setProperty("--rx", "0deg");
            card.style.setProperty("--ry", "0deg");
        });
    });
});
