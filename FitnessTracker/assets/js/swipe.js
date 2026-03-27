function setupSwipeCards(options) {
    const container = document.querySelector(options.container);
    if (!container) return;

    let startX = 0;
    let startY = 0;
    let currentCard = null;

    function handleGestureEnd(direction) {
        if (!currentCard) return;

        let sessionId = parseInt(currentCard.dataset.sessionId, 10);
        let exerciseId = parseInt(currentCard.dataset.exerciseId, 10);

        currentCard.style.transition = 'transform 0.2s ease, opacity 0.2s ease';
        currentCard.style.opacity = '0';
        currentCard.style.pointerEvents = 'none';

        setTimeout(function () {
            currentCard.remove();
        }, 220);

        if (typeof options.onSwipe === 'function' && sessionId > 0) {
            options.onSwipe(sessionId, exerciseId, direction);
        }

        currentCard = container.querySelector('.card');
    }

    function touchStart(event) {
        if (!event.target.closest('.card')) return;
        currentCard = event.target.closest('.card');
        startX = event.touches[0].clientX;
        startY = event.touches[0].clientY;
        currentCard.style.transition = 'none';
    }

    function touchMove(event) {
        if (!currentCard) return;
        const x = event.touches[0].clientX;
        const y = event.touches[0].clientY;
        const dx = x - startX;
        const dy = y - startY;

        if (Math.abs(dx) > Math.abs(dy) * 1.2) {
            event.preventDefault();
            const rotation = dx / 10;
            currentCard.style.transform = `translateX(${dx}px) rotate(${rotation}deg)`;
        }
    }

    function touchEnd(event) {
        if (!currentCard) return;
        const endX = event.changedTouches[0].clientX;
        const dx = endX - startX;

        if (Math.abs(dx) > 80) {
            const direction = dx > 0 ? 'right' : 'left';
            currentCard.style.transform = `translateX(${dx * 2}px) rotate(${dx > 0 ? 30 : -30}deg)`;
            handleGestureEnd(direction);
        } else {
            currentCard.style.transition = 'transform 0.2s ease';
            currentCard.style.transform = 'translateX(0) rotate(0)';
        }
    }

    container.addEventListener('touchstart', touchStart, {passive:true});
    container.addEventListener('touchmove', touchMove, {passive:false});
    container.addEventListener('touchend', touchEnd);

    // fallback click buttons (optionnel)
    if (options.nextButton || options.skipButton) {
        document.querySelector(options.nextButton)?.addEventListener('click', () => handleGestureEnd('right'));
        document.querySelector(options.skipButton)?.addEventListener('click', () => handleGestureEnd('left'));
    }
}
