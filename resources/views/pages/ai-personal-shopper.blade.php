<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="csrf-token-here">
    <title>Modern Admin Dashboard</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Toastr CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<style>
:root {
    --primary-color: #6f42c1;
    --secondary-color: #6c757d;
    --success-color: #198754;
    --info-color: #0dcaf0;
    --warning-color: #ffc107;
    --danger-color: #dc3545;
    --light-color: #f8f9fa;
    --dark-color: #212529;
    --ai-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    --ai-gradient-alt: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    --quiz-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
}

.hero-section {
    background: var(--ai-gradient);
    color: white;
    padding: 100px 0 80px;
    position: relative;
    overflow: hidden;
}

.hero-section::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="75" cy="75" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="25" cy="75" r="1" fill="rgba(255,255,255,0.05)"/><circle cx="75" cy="25" r="1" fill="rgba(255,255,255,0.05)"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>') repeat;
    animation: float 20s infinite linear;
}

@keyframes float {
    0% { transform: translate(0, 0); }
    100% { transform: translate(-100px, -100px); }
}

.ai-icon {
    font-size: 5rem;
    margin-bottom: 2rem;
    animation: pulse 2s infinite;
    filter: drop-shadow(0 0 20px rgba(255, 255, 255, 0.3));
}

@keyframes pulse {
    0%, 100% { transform: scale(1); opacity: 1; }
    50% { transform: scale(1.1); opacity: 0.8; }
}

.quiz-container {
    background: white;
    border-radius: 25px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
    padding: 3rem;
    margin: -80px auto 0;
    position: relative;
    z-index: 10;
    max-width: 800px;
}

.progress-bar-custom {
    height: 8px;
    border-radius: 4px;
    background: var(--quiz-gradient);
    transition: width 0.5s ease-in-out;
}

.quiz-step {
    display: none;
    animation: fadeIn 0.5s ease-in-out;
}

.quiz-step.active {
    display: block;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

.quiz-option {
    border: 2px solid #e9ecef;
    border-radius: 15px;
    padding: 1.5rem;
    margin-bottom: 1rem;
    cursor: pointer;
    transition: all 0.3s ease;
    background: white;
    position: relative;
    overflow: hidden;
}

.quiz-option::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(111, 66, 193, 0.1), transparent);
    transition: left 0.5s ease;
}

.quiz-option:hover {
    border-color: var(--primary-color);
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(111, 66, 193, 0.2);
}

.quiz-option:hover::before {
    left: 100%;
}

.quiz-option.selected {
    border-color: var(--primary-color);
    background: linear-gradient(135deg, rgba(111, 66, 193, 0.1), rgba(118, 75, 162, 0.1));
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(111, 66, 193, 0.3);
}

.quiz-option .option-icon {
    font-size: 2rem;
    color: var(--primary-color);
    margin-bottom: 1rem;
}

.quiz-option.selected .option-icon {
    animation: bounce 0.6s ease;
}

@keyframes bounce {
    0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
    40% { transform: translateY(-10px); }
    60% { transform: translateY(-5px); }
}

.btn-ai {
    background: var(--ai-gradient);
    border: none;
    border-radius: 50px;
    padding: 1rem 2.5rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.btn-ai::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: var(--ai-gradient-alt);
    transition: left 0.3s ease;
}

.btn-ai span {
    position: relative;
    z-index: 2;
}

.btn-ai:hover::before {
    left: 0;
}

.btn-ai:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 30px rgba(111, 66, 193, 0.4);
}

.recommendations-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 2rem;
    margin-top: 3rem;
}

.product-card {
    background: white;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
    position: relative;
}

.product-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 20px 50px rgba(0, 0, 0, 0.2);
}

.results-section {
    display: none;
}

.results-section.active {
    display: block;
    animation: fadeInUp 0.8s ease-out;
}

@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(30px); }
    to { opacity: 1; transform: translateY(0); }
}

.personality-card {
    background: var(--ai-gradient);
    color: white;
    border-radius: 20px;
    padding: 2rem;
    text-align: center;
    margin-bottom: 3rem;
    position: relative;
    overflow: hidden;
}

.personality-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="20" cy="20" r="2" fill="rgba(255,255,255,0.1)"/><circle cx="80" cy="80" r="2" fill="rgba(255,255,255,0.1)"/><circle cx="80" cy="20" r="1" fill="rgba(255,255,255,0.05)"/><circle cx="20" cy="80" r="1" fill="rgba(255,255,255,0.05)"/></svg>') repeat;
}

.step-indicator {
    display: flex;
    justify-content: center;
    margin-bottom: 2rem;
}

.step-dot {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background: #ddd;
    margin: 0 5px;
    transition: all 0.3s ease;
}

.step-dot.active {
    background: var(--primary-color);
    transform: scale(1.2);
}

.step-dot.completed {
    background: var(--success-color);
}

.loading-animation {
    display: none;
    text-align: center;
    padding: 3rem 0;
}

.loading-spinner {
    width: 60px;
    height: 60px;
    border: 4px solid #f3f3f3;
    border-top: 4px solid var(--primary-color);
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin: 0 auto 1rem;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.ai-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 1rem;
    margin: 2rem 0;
}

.stat-item {
    text-align: center;
    padding: 1rem;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 15px;
    backdrop-filter: blur(10px);
}

.stat-number {
    font-size: 2rem;
    font-weight: bold;
    color: white;
}

.stat-label {
    font-size: 0.9rem;
    color: rgba(255, 255, 255, 0.8);
    margin-top: 0.5rem;
}

@media (max-width: 768px) {
    .hero-section {
        padding: 80px 0 60px;
    }
    
    .quiz-container {
        margin: -60px 1rem 0;
        padding: 2rem 1.5rem;
    }
    
    .ai-icon {
        font-size: 3.5rem;
    }
    
    .quiz-option {
        padding: 1rem;
    }
    
    .recommendations-grid {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
}
</style>

<div class="hero-section">
    <div class="container position-relative">
        <div class="row justify-content-center text-center">
            <div class="col-lg-10">
                <div class="ai-icon">
                    <i class="fas fa-robot"></i>
                </div>
                <h1 class="display-3 fw-bold mb-4">AI Personal Shopper</h1>
                <p class="lead mb-4 fs-4">
                    Discover your unique style with our intelligent recommendation engine. 
                    Take our personalized quiz and let AI curate the perfect products just for you.
                </p>
                
                <!-- AI Stats -->
                <div class="ai-stats">
                    <div class="stat-item">
                        <div class="stat-number">98%</div>
                        <div class="stat-label">Accuracy Rate</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number">50K+</div>
                        <div class="stat-label">Happy Customers</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number">2M+</div>
                        <div class="stat-label">Recommendations</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number">24/7</div>
                        <div class="stat-label">AI Assistance</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container">
    <!-- Quiz Container -->
    <div class="quiz-container">
        <!-- Progress Bar -->
        <div class="mb-4">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted">Progress</span>
                <span class="text-muted"><span id="current-step">1</span> of <span id="total-steps">6</span></span>
            </div>
            <div class="progress" style="height: 8px;">
                <div id="quiz-progress" class="progress-bar-custom" role="progressbar" style="width: 16.67%" aria-valuenow="16.67" aria-valuemin="0" aria-valuemax="100"></div>
            </div>
        </div>

        <!-- Step Indicators -->
        <div class="step-indicator">
            <div class="step-dot active" data-step="1"></div>
            <div class="step-dot" data-step="2"></div>
            <div class="step-dot" data-step="3"></div>
            <div class="step-dot" data-step="4"></div>
            <div class="step-dot" data-step="5"></div>
            <div class="step-dot" data-step="6"></div>
        </div>

        <form id="aiShopperForm" style="display: none;">
            @csrf
            <input type="hidden" name="category" id="form_category">
            <input type="hidden" name="price_range" id="form_price_range">
            <input type="hidden" name="occasion" id="form_occasion">
            <input type="hidden" name="style" id="form_style">
            <input type="hidden" name="colors" id="form_colors">
            <input type="hidden" name="size" id="form_size">
        </form>

        <!-- Quiz Steps -->
        
        <!-- Step 1: Category -->
        <div class="quiz-step active" id="step-1">
            <h2 class="h3 text-center mb-4 text-primary">What type of products are you looking for?</h2>
            <p class="text-center text-muted mb-4">Choose the category that interests you most</p>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <div class="quiz-option" data-value="clothing" data-question="category">
                        <div class="text-center">
                            <div class="option-icon">
                                <i class="fas fa-tshirt"></i>
                            </div>
                            <h4 class="h5 mb-2">Clothing</h4>
                            <p class="text-muted mb-0">T-shirts, shirts, dresses, tops & more</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <div class="quiz-option" data-value="footwear" data-question="category">
                        <div class="text-center">
                            <div class="option-icon">
                                <i class="fas fa-shoe-prints"></i>
                            </div>
                            <h4 class="h5 mb-2">Footwear</h4>
                            <p class="text-muted mb-0">Sneakers, boots, heels, sandals & more</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <div class="quiz-option" data-value="accessories" data-question="category">
                        <div class="text-center">
                            <div class="option-icon">
                                <i class="fas fa-glasses"></i>
                            </div>
                            <h4 class="h5 mb-2">Accessories</h4>
                            <p class="text-muted mb-0">Watches, sunglasses, belts & more</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <div class="quiz-option" data-value="bags" data-question="category">
                        <div class="text-center">
                            <div class="option-icon">
                                <i class="fas fa-shopping-bag"></i>
                            </div>
                            <h4 class="h5 mb-2">Bags</h4>
                            <p class="text-muted mb-0">Handbags, backpacks, clutches & more</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Step 2: Price Range -->
        <div class="quiz-step" id="step-2">
            <h2 class="h3 text-center mb-4 text-primary">What's your budget range?</h2>
            <p class="text-center text-muted mb-4">Help us recommend products within your price range</p>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <div class="quiz-option" data-value="0-999" data-question="price_range">
                        <div class="text-center">
                            <div class="option-icon" style="color: #27ae60;">
                                <i class="fas fa-rupee-sign"></i>
                            </div>
                            <h4 class="h5 mb-2">Budget Friendly</h4>
                            <p class="text-muted mb-0">Under ₹999</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <div class="quiz-option" data-value="1000-1999" data-question="price_range">
                        <div class="text-center">
                            <div class="option-icon" style="color: #f39c12;">
                                <i class="fas fa-coins"></i>
                            </div>
                            <h4 class="h5 mb-2">Affordable</h4>
                            <p class="text-muted mb-0">₹1,000 - ₹1,999</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <div class="quiz-option" data-value="2000-4999" data-question="price_range">
                        <div class="text-center">
                            <div class="option-icon" style="color: #9b59b6;">
                                <i class="fas fa-gem"></i>
                            </div>
                            <h4 class="h5 mb-2">Mid-Range</h4>
                            <p class="text-muted mb-0">₹2,000 - ₹4,999</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <div class="quiz-option" data-value="5000-99999" data-question="price_range">
                        <div class="text-center">
                            <div class="option-icon" style="color: #e67e22;">
                                <i class="fas fa-crown"></i>
                            </div>
                            <h4 class="h5 mb-2">Premium</h4>
                            <p class="text-muted mb-0">₹5,000+</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Step 3: Occasion -->
        <div class="quiz-step" id="step-3">
            <h2 class="h3 text-center mb-4 text-primary">What occasion are you shopping for?</h2>
            <p class="text-center text-muted mb-4">Choose the occasion that best fits your needs</p>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <div class="quiz-option" data-value="casual" data-question="occasion">
                        <div class="text-center">
                            <div class="option-icon">
                                <i class="fas fa-coffee"></i>
                            </div>
                            <h4 class="h5 mb-2">Casual</h4>
                            <p class="text-muted mb-0">Everyday wear, relaxed outings</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <div class="quiz-option" data-value="formal" data-question="occasion">
                        <div class="text-center">
                            <div class="option-icon">
                                <i class="fas fa-briefcase"></i>
                            </div>
                            <h4 class="h5 mb-2">Formal</h4>
                            <p class="text-muted mb-0">Office wear, business meetings</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <div class="quiz-option" data-value="party" data-question="occasion">
                        <div class="text-center">
                            <div class="option-icon">
                                <i class="fas fa-glass-cheers"></i>
                            </div>
                            <h4 class="h5 mb-2">Party</h4>
                            <p class="text-muted mb-0">Parties, celebrations, nightouts</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <div class="quiz-option" data-value="sports" data-question="occasion">
                        <div class="text-center">
                            <div class="option-icon">
                                <i class="fas fa-running"></i>
                            </div>
                            <h4 class="h5 mb-2">Sports</h4>
                            <p class="text-muted mb-0">Gym, outdoor activities, fitness</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Step 4: Style -->
        <div class="quiz-step" id="step-4">
            <h2 class="h3 text-center mb-4 text-primary">What's your style preference?</h2>
            <p class="text-center text-muted mb-4">Choose the style that resonates with your personality</p>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <div class="quiz-option" data-value="classic" data-question="style">
                        <div class="text-center">
                            <div class="option-icon">
                                <i class="fas fa-gem"></i>
                            </div>
                            <h4 class="h5 mb-2">Classic</h4>
                            <p class="text-muted mb-0">Timeless, elegant, sophisticated</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <div class="quiz-option" data-value="trendy" data-question="style">
                        <div class="text-center">
                            <div class="option-icon">
                                <i class="fas fa-fire"></i>
                            </div>
                            <h4 class="h5 mb-2">Trendy</h4>
                            <p class="text-muted mb-0">Latest fashion, bold, contemporary</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <div class="quiz-option" data-value="bohemian" data-question="style">
                        <div class="text-center">
                            <div class="option-icon">
                                <i class="fas fa-feather"></i>
                            </div>
                            <h4 class="h5 mb-2">Bohemian</h4>
                            <p class="text-muted mb-0">Free-spirited, artistic, unique</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <div class="quiz-option" data-value="minimalist" data-question="style">
                        <div class="text-center">
                            <div class="option-icon">
                                <i class="fas fa-circle"></i>
                            </div>
                            <h4 class="h5 mb-2">Minimalist</h4>
                            <p class="text-muted mb-0">Clean lines, simple, understated</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Step 5: Colors -->
        <div class="quiz-step" id="step-5">
            <h2 class="h3 text-center mb-4 text-primary">What colors do you prefer?</h2>
            <p class="text-center text-muted mb-4">Select your favorite colors (you can choose multiple)</p>
            
            <div class="row">
                <div class="col-md-4 mb-3">
                    <div class="quiz-option" data-value="black" data-question="colors" data-multi="true">
                        <div class="text-center">
                            <div class="option-icon" style="color: #2c3e50;">
                                <i class="fas fa-circle"></i>
                            </div>
                            <h4 class="h5 mb-2">Black</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="quiz-option" data-value="white" data-question="colors" data-multi="true">
                        <div class="text-center">
                            <div class="option-icon" style="color: #ecf0f1;">
                                <i class="fas fa-circle"></i>
                            </div>
                            <h4 class="h5 mb-2">White</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="quiz-option" data-value="blue" data-question="colors" data-multi="true">
                        <div class="text-center">
                            <div class="option-icon" style="color: #3498db;">
                                <i class="fas fa-circle"></i>
                            </div>
                            <h4 class="h5 mb-2">Blue</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="quiz-option" data-value="red" data-question="colors" data-multi="true">
                        <div class="text-center">
                            <div class="option-icon" style="color: #e74c3c;">
                                <i class="fas fa-circle"></i>
                            </div>
                            <h4 class="h5 mb-2">Red</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="quiz-option" data-value="green" data-question="colors" data-multi="true">
                        <div class="text-center">
                            <div class="option-icon" style="color: #27ae60;">
                                <i class="fas fa-circle"></i>
                            </div>
                            <h4 class="h5 mb-2">Green</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="quiz-option" data-value="pink" data-question="colors" data-multi="true">
                        <div class="text-center">
                            <div class="option-icon" style="color: #e91e63;">
                                <i class="fas fa-circle"></i>
                            </div>
                            <h4 class="h5 mb-2">Pink</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Step 6: Size -->
        <div class="quiz-step" id="step-6">
            <h2 class="h3 text-center mb-4 text-primary">What's your preferred size?</h2>
            <p class="text-center text-muted mb-4">Choose the size that fits you best</p>
            
            <div class="row justify-content-center">
                <div class="col-md-4 mb-3">
                    <div class="quiz-option" data-value="S" data-question="size">
                        <div class="text-center">
                            <div class="option-icon">
                                <i class="fas fa-compress-alt"></i>
                            </div>
                            <h4 class="h5 mb-2">Small (S)</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="quiz-option" data-value="M" data-question="size">
                        <div class="text-center">
                            <div class="option-icon">
                                <i class="fas fa-expand-alt"></i>
                            </div>
                            <h4 class="h5 mb-2">Medium (M)</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="quiz-option" data-value="L" data-question="size">
                        <div class="text-center">
                            <div class="option-icon">
                                <i class="fas fa-arrows-alt"></i>
                            </div>
                            <h4 class="h5 mb-2">Large (L)</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="quiz-option" data-value="XL" data-question="size">
                        <div class="text-center">
                            <div class="option-icon">
                                <i class="fas fa-arrows-alt-h"></i>
                            </div>
                            <h4 class="h5 mb-2">Extra Large (XL)</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="quiz-option" data-value="XXL" data-question="size">
                        <div class="text-center">
                            <div class="option-icon">
                                <i class="fas fa-arrows-alt-v"></i>
                            </div>
                            <h4 class="h5 mb-2">XXL</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Loading Animation -->
        <div class="loading-animation" id="loading-section">
            <div class="loading-spinner"></div>
            <h3 class="h4 text-primary mb-3">Analyzing Your Style Profile...</h3>
            <p class="text-muted mb-4">Our AI is processing your preferences to find the perfect matches</p>
            <div class="progress" style="max-width: 300px; margin: 0 auto;">
                <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary" role="progressbar" style="width: 100%"></div>
            </div>
        </div>

        <!-- Navigation Buttons -->
        <div class="d-flex justify-content-between mt-4" id="quiz-navigation">
            <button class="btn btn-outline-secondary" id="prev-btn" disabled>
                <i class="fas fa-arrow-left me-2"></i>Previous
            </button>
            <button class="btn btn-ai text-white" id="next-btn" disabled>
                <span>Next <i class="fas fa-arrow-right ms-2"></i></span>
            </button>
        </div>
    </div>

    <!-- Results Section -->
    <div class="results-section" id="results-section">
        <!-- Personality Card -->
        <div class="personality-card">
            <h2 class="h3 mb-3">Your AI Recommendations</h2>
            <p class="lead mb-0">
                Based on your preferences, we've curated the perfect products just for you!
            </p>
        </div>

        <!-- Recommendations Grid -->
        <div class="recommendations-grid" id="recommendations-grid">
            <!-- Products will be dynamically inserted here -->
        </div>

        <!-- Restart Quiz Button -->
        <div class="text-center mt-4">
            <button class="btn btn-outline-primary" onclick="restartQuiz()">
                <i class="fas fa-redo me-2"></i>Take Quiz Again
            </button>
        </div>
    </div>
</div>

<script>
class AIPersonalShopper {
    constructor() {
        this.currentStep = 1;
        this.totalSteps = 6;
        this.answers = {};
        this.selectedColors = [];
        this.init();
    }

    init() {
        this.bindEvents();
        this.updateUI();
    }

    bindEvents() {
        // Quiz option selection
        document.querySelectorAll('.quiz-option').forEach(option => {
            option.addEventListener('click', (e) => {
                const question = e.currentTarget.dataset.question;
                const value = e.currentTarget.dataset.value;
                const isMulti = e.currentTarget.dataset.multi === 'true';
                this.selectOption(question, value, e.currentTarget, isMulti);
            });
        });

        // Navigation buttons
        document.getElementById('prev-btn').addEventListener('click', () => this.previousStep());
        document.getElementById('next-btn').addEventListener('click', () => this.nextStep());
    }

    selectOption(question, value, element, isMulti = false) {
        if (isMulti && question === 'colors') {
            // Handle multiple color selection
            if (element.classList.contains('selected')) {
                element.classList.remove('selected');
                this.selectedColors = this.selectedColors.filter(c => c !== value);
            } else {
                element.classList.add('selected');
                this.selectedColors.push(value);
            }
            this.answers[question] = this.selectedColors;
        } else {
            // Single selection
            const questionOptions = document.querySelectorAll(`[data-question="${question}"]`);
            questionOptions.forEach(opt => opt.classList.remove('selected'));
            
            element.classList.add('selected');
            this.answers[question] = value;
        }

        // Enable next button if we have selection
        const hasSelection = (question === 'colors' && this.selectedColors.length > 0) || 
                           (question !== 'colors' && this.answers[question]);
        document.getElementById('next-btn').disabled = !hasSelection;

        // Auto-advance for single selections (but not colors)
        if (!isMulti && question !== 'colors') {
            setTimeout(() => {
                if (this.currentStep < this.totalSteps) {
                    this.nextStep();
                }
            }, 500);
        }
    }

    nextStep() {
        if (this.currentStep < this.totalSteps) {
            document.getElementById(`step-${this.currentStep}`).classList.remove('active');
            this.currentStep++;
            document.getElementById(`step-${this.currentStep}`).classList.add('active');
            this.updateUI();
        } else {
            this.generateRecommendations();
        }
    }

    previousStep() {
        if (this.currentStep > 1) {
            document.getElementById(`step-${this.currentStep}`).classList.remove('active');
            this.currentStep--;
            document.getElementById(`step-${this.currentStep}`).classList.add('active');
            this.updateUI();
        }
    }

    updateUI() {
        // Update progress bar
        const progress = (this.currentStep / this.totalSteps) * 100;
        document.getElementById('quiz-progress').style.width = `${progress}%`;
        document.getElementById('quiz-progress').setAttribute('aria-valuenow', progress);

        // Update step counter
        document.getElementById('current-step').textContent = this.currentStep;

        // Update step indicators
        document.querySelectorAll('.step-dot').forEach((dot, index) => {
            dot.classList.remove('active', 'completed');
            if (index + 1 === this.currentStep) {
                dot.classList.add('active');
            } else if (index + 1 < this.currentStep) {
                dot.classList.add('completed');
            }
        });

        // Update navigation buttons
        document.getElementById('prev-btn').disabled = this.currentStep === 1;
        
        // Check if current step has selection
        const currentStepElement = document.getElementById(`step-${this.currentStep}`);
        const question = currentStepElement.querySelector('.quiz-option').dataset.question;
        const hasSelection = (question === 'colors' && this.selectedColors.length > 0) || 
                           (question !== 'colors' && this.answers[question]);
        
        if (this.currentStep === this.totalSteps) {
            document.getElementById('next-btn').innerHTML = '<span>Get My Recommendations <i class="fas fa-magic ms-2"></i></span>';
        } else {
            document.getElementById('next-btn').innerHTML = '<span>Next <i class="fas fa-arrow-right ms-2"></i></span>';
        }
        
        document.getElementById('next-btn').disabled = !hasSelection;
    }

    generateRecommendations() {
        // Update form fields
        document.getElementById('form_category').value = this.answers.category || '';
        document.getElementById('form_price_range').value = this.answers.price_range || '';
        document.getElementById('form_occasion').value = this.answers.occasion || '';
        document.getElementById('form_style').value = this.answers.style || '';
        document.getElementById('form_colors').value = this.selectedColors.join(',');
        document.getElementById('form_size').value = this.answers.size || '';

        // Show loading
        document.getElementById('quiz-navigation').style.display = 'none';
        document.querySelectorAll('.quiz-step').forEach(step => step.style.display = 'none');
        document.getElementById('loading-section').style.display = 'block';

        // Submit AJAX request
        const formData = new FormData(document.getElementById('aiShopperForm'));
        
        fetch('{{ route("pages.ai.recommendations") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            this.displayResults(data);
        })
        .catch(error => {
            console.error('Error:', error);
            this.displayError();
        });
    }

    displayResults(data) {
        // Hide loading and quiz
        document.getElementById('loading-section').style.display = 'none';
        document.querySelector('.quiz-container').style.display = 'none';
        
        if (data.success) {
            document.getElementById('recommendations-grid').innerHTML = data.html;
        } else {
            document.getElementById('recommendations-grid').innerHTML = '<div class="text-center py-5"><p class="text-muted">No products found matching your preferences. Try different selections.</p></div>';
        }

        // Show results
        document.getElementById('results-section').classList.add('active');

        // Scroll to results
        document.getElementById('results-section').scrollIntoView({ 
            behavior: 'smooth', 
            block: 'start' 
        });
    }

    displayError() {
        document.getElementById('loading-section').style.display = 'none';
        document.getElementById('recommendations-grid').innerHTML = '<div class="text-center py-5"><p class="text-danger">Something went wrong. Please try again.</p></div>';
        document.getElementById('results-section').classList.add('active');
    }
}

// Global functions
function restartQuiz() {
    // Reset quiz state
    document.querySelector('.quiz-container').style.display = 'block';
    document.getElementById('results-section').classList.remove('active');
    
    // Scroll back to quiz
    document.querySelector('.quiz-container').scrollIntoView({ 
        behavior: 'smooth', 
        block: 'start' 
    });
    
    // Reinitialize
    setTimeout(() => {
        window.aiShopper = new AIPersonalShopper();
    }, 500);
}

// Initialize the quiz when page loads
document.addEventListener('DOMContentLoaded', function() {
    window.aiShopper = new AIPersonalShopper();
});
</script>
</body>

</html>