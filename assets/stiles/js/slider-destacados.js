var slider = document.querySelector('.destacados-slider');

if (slider) {
    var track = slider.querySelector('.row');
    var slides = track.querySelectorAll('[class*="col-"]');

    var dotsWrap = document.createElement('div');
    dotsWrap.className = 'slider-dots';
    for (var d = 0; d < slides.length; d++) {
        var dot = document.createElement('span');
        dot.className = 'dot';
        dotsWrap.appendChild(dot);
    }
    slider.appendChild(dotsWrap);
    var dots = dotsWrap.querySelectorAll('.dot');

    for (var k = 0; k < dots.length; k++) {
        (function (idx) {
            dots[idx].addEventListener('click', function () {
                slides[idx].scrollIntoView({
                    behavior: 'smooth',
                    inline: 'center',
                    block: 'nearest'
                });
            });
        })(k);
    }

    if ('IntersectionObserver' in window) {
        var observer = new IntersectionObserver(function (entries) {
            for (var i = 0; i < entries.length; i++) {
                var entry = entries[i];
                var idx = Array.prototype.indexOf.call(slides, entry.target);
                if (idx === -1) continue;

                if (entry.isIntersecting && entry.intersectionRatio > 0.6) {
                    slides[idx].classList.add('activa');
                    dots[idx].classList.add('activo');
                } else {
                    slides[idx].classList.remove('activa');
                    dots[idx].classList.remove('activo');
                }
            }
        }, {
            root: track,
            threshold: [0, 0.6, 1]
        });

        for (var s = 0; s < slides.length; s++) {
            observer.observe(slides[s]);
        }
    }
}