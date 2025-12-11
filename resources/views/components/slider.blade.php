<!-- Hero Slider Component -->
@if($sliders && $sliders->count() > 0)
<div id="heroSlider" class="carousel slide carousel-fade" data-bs-ride="carousel" data-bs-interval="5000">
    <!-- Carousel Indicators -->
    <div class="carousel-indicators">
        @foreach($sliders as $index => $slider)
            <button type="button" 
                    data-bs-target="#heroSlider" 
                    data-bs-slide-to="{{ $index }}" 
                    class="{{ $index === 0 ? 'active' : '' }}"
                    aria-current="{{ $index === 0 ? 'true' : 'false' }}" 
                    aria-label="Slide {{ $index + 1 }}"></button>
        @endforeach
    </div>

    <!-- Carousel Items -->
    <div class="carousel-inner">
        @foreach($sliders as $index => $slider)
            <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
                <!-- Responsive Image with Optimization -->
                <picture>
                    <!-- WebP for modern browsers -->
                    <source media="(min-width: 1200px)" 
                            srcset="{{ $slider->getThumbnailUrl(1200) }}" 
                            type="image/webp">
                    <source media="(min-width: 992px)" 
                            srcset="{{ $slider->getThumbnailUrl(900) }}" 
                            type="image/webp">
                    <source media="(min-width: 768px)" 
                            srcset="{{ $slider->getThumbnailUrl(600) }}" 
                            type="image/webp">
                    <source media="(max-width: 767px)" 
                            srcset="{{ $slider->getThumbnailUrl(300) }}" 
                            type="image/webp">
                    
                    <!-- Fallback for older browsers -->
                    <img src="{{ $slider->getThumbnailUrl(1200) }}" 
                         class="d-block w-100 slider-image" 
                         alt="{{ $slider->title ?: 'Slider Image' }}"
                         loading="{{ $index === 0 ? 'eager' : 'lazy' }}">
                </picture>

                <!-- Overlay Content -->
                @if($slider->title || $slider->description)
                    <div class="carousel-caption d-none d-md-block">
                        <div class="slider-content">
                            @if($slider->title)
                                <h2 class="slider-title animate__animated animate__fadeInUp">
                                    {{ $slider->title }}
                                </h2>
                            @endif
                            
                            @if($slider->description)
                                <p class="slider-description animate__animated animate__fadeInUp animate__delay-1s">
                                    {{ $slider->description }}
                                </p>
                            @endif
                            
                            @if($slider->link)
                                <div class="slider-actions animate__animated animate__fadeInUp animate__delay-2s">
                                    <a href="{{ $slider->link }}" 
                                       class="btn btn-primary btn-lg slider-btn"
                                       target="_blank">
                                        Learn More
                                        <i class="bi bi-arrow-right ms-2"></i>
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                <!-- Mobile Overlay -->
                @if($slider->title || $slider->description)
                    <div class="carousel-caption d-md-none mobile-caption">
                        @if($slider->title)
                            <h5 class="slider-title-mobile">{{ $slider->title }}</h5>
                        @endif
                        @if($slider->link)
                            <a href="{{ $slider->link }}" 
                               class="btn btn-primary btn-sm mt-2"
                               target="_blank">
                                View More
                            </a>
                        @endif
                    </div>
                @endif

                <!-- Click Link Overlay (if link exists) -->
                @if($slider->link)
                    <a href="{{ $slider->link }}" 
                       class="slider-link-overlay"
                       target="_blank"
                       aria-label="Go to {{ $slider->title ?: 'link' }}"></a>
                @endif
            </div>
        @endforeach
    </div>

    <!-- Navigation Controls -->
    @if($sliders->count() > 1)
        <button class="carousel-control-prev" type="button" data-bs-target="#heroSlider" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Previous</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#heroSlider" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Next</span>
        </button>
    @endif
</div>
@endif

<style>
/* Slider Styles */
#heroSlider {
    position: relative;
    margin-bottom: 2rem;
}

.slider-image {
    height: 500px;
    object-fit: cover;
    object-position: center;
}

.carousel-caption {
    background: linear-gradient(to top, rgba(0,0,0,0.7), transparent);
    bottom: 0;
    left: 0;
    right: 0;
    padding: 3rem 2rem 2rem;
    text-align: left;
}

.slider-content {
    max-width: 600px;
}

.slider-title {
    font-size: 3rem;
    font-weight: 700;
    color: #fff;
    text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
    margin-bottom: 1rem;
    line-height: 1.2;
}

.slider-description {
    font-size: 1.25rem;
    color: #f8f9fa;
    margin-bottom: 2rem;
    text-shadow: 1px 1px 2px rgba(0,0,0,0.5);
}

.slider-btn {
    background: linear-gradient(135deg, #007bff, #0056b3);
    border: none;
    padding: 12px 30px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(0,123,255,0.3);
}

.slider-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0,123,255,0.4);
    background: linear-gradient(135deg, #0056b3, #004085);
}

/* Mobile Styles */
.mobile-caption {
    background: rgba(0,0,0,0.7);
    bottom: 20px;
    left: 20px;
    right: 20px;
    border-radius: 10px;
    padding: 1rem;
}

.slider-title-mobile {
    font-size: 1.25rem;
    font-weight: 600;
    margin-bottom: 0.5rem;
    color: #fff;
}

/* Invisible link overlay for full slider click */
.slider-link-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    z-index: 5;
    background: transparent;
    text-decoration: none;
}

/* Carousel Controls */
.carousel-control-prev,
.carousel-control-next {
    width: 60px;
    height: 60px;
    top: 50%;
    transform: translateY(-50%);
    background: rgba(0,0,0,0.5);
    border-radius: 50%;
    border: 2px solid rgba(255,255,255,0.3);
    transition: all 0.3s ease;
}

.carousel-control-prev {
    left: 20px;
}

.carousel-control-next {
    right: 20px;
}

.carousel-control-prev:hover,
.carousel-control-next:hover {
    background: rgba(0,0,0,0.7);
    border-color: #fff;
}

.carousel-control-prev-icon,
.carousel-control-next-icon {
    width: 20px;
    height: 20px;
}

/* Carousel Indicators */
.carousel-indicators {
    bottom: 20px;
    margin-bottom: 0;
}

.carousel-indicators [data-bs-target] {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background: rgba(255,255,255,0.5);
    border: 2px solid transparent;
    transition: all 0.3s ease;
}

.carousel-indicators .active {
    background: #fff;
    border-color: #007bff;
}

/* Responsive Design */
@media (max-width: 768px) {
    .slider-image {
        height: 300px;
    }
    
    .slider-title {
        font-size: 2rem;
    }
    
    .slider-description {
        font-size: 1rem;
        margin-bottom: 1rem;
    }
    
    .carousel-control-prev,
    .carousel-control-next {
        width: 40px;
        height: 40px;
    }
    
    .carousel-control-prev {
        left: 10px;
    }
    
    .carousel-control-next {
        right: 10px;
    }
}

@media (max-width: 576px) {
    .slider-image {
        height: 250px;
    }
    
    .carousel-caption {
        padding: 2rem 1rem 1rem;
    }
    
    .slider-title {
        font-size: 1.5rem;
    }
}

/* Animation Classes */
.animate__animated {
    animation-duration: 1s;
    animation-fill-mode: both;
}

.animate__fadeInUp {
    animation-name: fadeInUp;
}

.animate__delay-1s {
    animation-delay: 0.3s;
}

.animate__delay-2s {
    animation-delay: 0.6s;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Preloader for images */
.slider-image[loading="lazy"] {
    transition: opacity 0.3s ease;
}

.slider-image[loading="lazy"]:not([src]) {
    opacity: 0;
}
</style>

<!-- Add to head for animation library (optional) -->
@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
@endpush