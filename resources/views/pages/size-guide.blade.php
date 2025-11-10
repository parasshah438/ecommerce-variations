<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Size Guide & Product Care - Your Company</title>
    
    <!-- Bootstrap 5 CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons CDN -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <!-- Custom CSS -->
    <style>
        :root {
            --primary-color: #007bff;
            --secondary-color: #6c757d;
            --accent-color: #fd7e14;
            --success-color: #28a745;
        }
        
        .hero-section {
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            color: white;
            padding: 100px 0 60px;
        }
        
        .guide-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            opacity: 0;
            transform: translateY(30px);
        }
        
        .guide-card.animate {
            opacity: 1;
            transform: translateY(0);
        }
        
        .guide-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
        }
        
        .size-chart {
            background: #f8f9fa;
            border-radius: 15px;
            overflow: hidden;
        }
        
        .measurement-tool {
            background: linear-gradient(135deg, #e3f2fd, #f3e5f5);
            border-radius: 15px;
            padding: 2rem;
            text-align: center;
        }
        
        .care-instruction {
            border-left: 5px solid var(--accent-color);
            background: #fff8e1;
            padding: 1.5rem;
            border-radius: 0 10px 10px 0;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
        }
        
        .care-instruction:hover {
            transform: translateX(10px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        .tab-content {
            animation: fadeIn 0.5s ease;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        
        .pulse-animation {
            animation: pulse 2s infinite;
        }
        
        .icon-xl {
            font-size: 4rem;
            margin-bottom: 1rem;
        }
        
        .ruler-animation {
            display: inline-block;
            animation: measure 3s ease-in-out infinite;
        }
        
        @keyframes measure {
            0%, 100% { transform: scaleX(1); }
            50% { transform: scaleX(1.2); }
        }
        
        .floating-element {
            animation: float 3s ease-in-out infinite;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
        
        .step-counter {
            background: var(--primary-color);
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-right: 1rem;
            flex-shrink: 0;
        }
        
        .size-calculator {
            background: linear-gradient(135deg, #fff3cd, #f8f9fa);
            border-radius: 15px;
            padding: 2rem;
        }
    </style>
</head>
<body>
    
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
        <div class="container">
            <a class="navbar-brand fw-bold" href="{{ route('welcome') }}">
                <i class="bi bi-shop"></i> Your Company
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('welcome') }}">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('products.index') }}">Products</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle active" href="#" role="button" data-bs-toggle="dropdown">
                            Guides
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item active" href="{{ route('pages.size.guide') }}">Size Guide</a></li>
                            <li><a class="dropdown-item" href="{{ route('pages.product.care') }}">Product Care</a></li>
                            <li><a class="dropdown-item" href="{{ route('pages.lookbook') }}">Lookbook</a></li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('pages.help') }}">Help & Support</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h1 class="display-4 fw-bold mb-4 floating-element">
                        <i class="bi bi-rulers me-3"></i>Size Guide & Care
                    </h1>
                    <p class="lead mb-4">
                        Find your perfect fit with our comprehensive sizing guide and learn how to 
                        care for your products to ensure they last longer.
                    </p>
                    <div class="d-flex gap-3 flex-wrap">
                        <a href="#size-guide" class="btn btn-light btn-lg pulse-animation">
                            <i class="bi bi-ruler me-2"></i>Find My Size
                        </a>
                        <a href="#care-guide" class="btn btn-outline-light btn-lg">
                            <i class="bi bi-heart me-2"></i>Care Instructions
                        </a>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="text-center">
                        <div class="ruler-animation">
                            <i class="bi bi-rulers text-white" style="font-size: 8rem;"></i>
                        </div>

                        <!-- Visual Size Guide with SVG -->
                        <div class="row mt-5">
                            <div class="col-12">
                                <h3 class="text-center mb-4">
                                    <i class="bi bi-diagram-2 text-primary me-2"></i>Visual Measurement Guide
                                </h3>
                                <div class="row">
                                    <!-- Women's Body Measurement SVG -->
                                    <div class="col-lg-6 mb-4">
                                        <div class="text-center bg-light rounded p-4">
                                            <h5 class="mb-3">Women's Measurements</h5>
                                            <svg width="200" height="300" viewBox="0 0 200 300" style="max-width: 100%;">
                                                <!-- Body outline -->
                                                <ellipse cx="100" cy="60" rx="15" ry="20" fill="#ffc107" stroke="#333" stroke-width="2"/>
                                                <rect x="85" y="80" width="30" height="80" fill="#e3f2fd" stroke="#333" stroke-width="2" rx="15"/>
                                                <rect x="75" y="160" width="50" height="100" fill="#e8f5e9" stroke="#333" stroke-width="2" rx="25"/>
                                                
                                                <!-- Measurement lines -->
                                                <!-- Bust -->
                                                <line x1="60" y1="100" x2="140" y2="100" stroke="#dc3545" stroke-width="2" marker-end="url(#arrowhead)"/>
                                                <line x1="140" y1="100" x2="60" y2="100" stroke="#dc3545" stroke-width="2" marker-end="url(#arrowhead)"/>
                                                <text x="145" y="105" font-size="12" fill="#dc3545">Bust</text>
                                                
                                                <!-- Waist -->
                                                <line x1="65" y1="140" x2="135" y2="140" stroke="#28a745" stroke-width="2" marker-end="url(#arrowhead)"/>
                                                <line x1="135" y1="140" x2="65" y2="140" stroke="#28a745" stroke-width="2" marker-end="url(#arrowhead)"/>
                                                <text x="140" y="145" font-size="12" fill="#28a745">Waist</text>
                                                
                                                <!-- Hip -->
                                                <line x1="55" y1="190" x2="145" y2="190" stroke="#6f42c1" stroke-width="2" marker-end="url(#arrowhead)"/>
                                                <line x1="145" y1="190" x2="55" y2="190" stroke="#6f42c1" stroke-width="2" marker-end="url(#arrowhead)"/>
                                                <text x="150" y="195" font-size="12" fill="#6f42c1">Hip</text>
                                                
                                                <!-- Arrow marker definition -->
                                                <defs>
                                                    <marker id="arrowhead" markerWidth="10" markerHeight="7" 
                                                            refX="9" refY="3.5" orient="auto">
                                                        <polygon points="0 0, 10 3.5, 0 7" fill="#333"/>
                                                    </marker>
                                                </defs>
                                            </svg>
                                        </div>
                                    </div>
                                    
                                    <!-- Men's Body Measurement SVG -->
                                    <div class="col-lg-6 mb-4">
                                        <div class="text-center bg-light rounded p-4">
                                            <h5 class="mb-3">Men's Measurements</h5>
                                            <svg width="200" height="300" viewBox="0 0 200 300" style="max-width: 100%;">
                                                <!-- Body outline -->
                                                <ellipse cx="100" cy="60" rx="18" ry="22" fill="#ffc107" stroke="#333" stroke-width="2"/>
                                                <rect x="80" y="82" width="40" height="90" fill="#e3f2fd" stroke="#333" stroke-width="2" rx="10"/>
                                                <rect x="85" y="172" width="30" height="100" fill="#e8f5e9" stroke="#333" stroke-width="2" rx="15"/>
                                                
                                                <!-- Measurement lines -->
                                                <!-- Chest -->
                                                <line x1="55" y1="110" x2="145" y2="110" stroke="#dc3545" stroke-width="2" marker-end="url(#arrowhead2)"/>
                                                <line x1="145" y1="110" x2="55" y2="110" stroke="#dc3545" stroke-width="2" marker-end="url(#arrowhead2)"/>
                                                <text x="150" y="115" font-size="12" fill="#dc3545">Chest</text>
                                                
                                                <!-- Waist -->
                                                <line x1="65" y1="150" x2="135" y2="150" stroke="#28a745" stroke-width="2" marker-end="url(#arrowhead2)"/>
                                                <line x1="135" y1="150" x2="65" y2="150" stroke="#28a745" stroke-width="2" marker-end="url(#arrowhead2)"/>
                                                <text x="140" y="155" font-size="12" fill="#28a745">Waist</text>
                                                
                                                <!-- Hip -->
                                                <line x1="70" y1="190" x2="130" y2="190" stroke="#6f42c1" stroke-width="2" marker-end="url(#arrowhead2)"/>
                                                <line x1="130" y1="190" x2="70" y2="190" stroke="#6f42c1" stroke-width="2" marker-end="url(#arrowhead2)"/>
                                                <text x="135" y="195" font-size="12" fill="#6f42c1">Hip</text>
                                                
                                                <!-- Arrow marker definition -->
                                                <defs>
                                                    <marker id="arrowhead2" markerWidth="10" markerHeight="7" 
                                                            refX="9" refY="3.5" orient="auto">
                                                        <polygon points="0 0, 10 3.5, 0 7" fill="#333"/>
                                                    </marker>
                                                </defs>
                                            </svg>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Size Comparison Visual -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="text-center bg-gradient p-4 rounded" style="background: linear-gradient(135deg, #f8f9fa, #e9ecef);">
                                    <h5 class="mb-3">Size Comparison Guide</h5>
                                    <div class="d-flex justify-content-center align-items-end flex-wrap gap-2">
                                        <div class="text-center">
                                            <svg width="30" height="40" viewBox="0 0 30 40">
                                                <rect x="10" y="10" width="10" height="30" fill="#17a2b8" rx="2"/>
                                                <text x="15" y="50" font-size="8" text-anchor="middle">XS</text>
                                            </svg>
                                        </div>
                                        <div class="text-center">
                                            <svg width="35" height="45" viewBox="0 0 35 45">
                                                <rect x="12" y="8" width="11" height="35" fill="#28a745" rx="2"/>
                                                <text x="17" y="50" font-size="8" text-anchor="middle">S</text>
                                            </svg>
                                        </div>
                                        <div class="text-center">
                                            <svg width="40" height="50" viewBox="0 0 40 50">
                                                <rect x="14" y="6" width="12" height="40" fill="#ffc107" rx="2"/>
                                                <text x="20" y="52" font-size="8" text-anchor="middle">M</text>
                                            </svg>
                                        </div>
                                        <div class="text-center">
                                            <svg width="45" height="55" viewBox="0 0 45 55">
                                                <rect x="16" y="4" width="13" height="45" fill="#fd7e14" rx="2"/>
                                                <text x="22" y="55" font-size="8" text-anchor="middle">L</text>
                                            </svg>
                                        </div>
                                        <div class="text-center">
                                            <svg width="50" height="60" viewBox="0 0 50 60">
                                                <rect x="18" y="2" width="14" height="50" fill="#dc3545" rx="2"/>
                                                <text x="25" y="58" font-size="8" text-anchor="middle">XL</text>
                                            </svg>
                                        </div>
                                        <div class="text-center">
                                            <svg width="55" height="65" viewBox="0 0 55 65">
                                                <rect x="20" y="0" width="15" height="55" fill="#6f42c1" rx="2"/>
                                                <text x="27" y="62" font-size="8" text-anchor="middle">XXL</text>
                                            </svg>
                                        </div>
                                        <div class="text-center">
                                            <svg width="60" height="70" viewBox="0 0 60 70">
                                                <rect x="22" y="0" width="16" height="60" fill="#6610f2" rx="2"/>
                                                <text x="30" y="67" font-size="8" text-anchor="middle">3XL</text>
                                            </svg>
                                        </div>
                                        <div class="text-center">
                                            <svg width="65" height="75" viewBox="0 0 65 75">
                                                <rect x="24" y="0" width="17" height="65" fill="#e83e8c" rx="2"/>
                                                <text x="32" y="72" font-size="8" text-anchor="middle">4XL</text>
                                            </svg>
                                        </div>
                                        <div class="text-center">
                                            <svg width="70" height="80" viewBox="0 0 70 80">
                                                <rect x="26" y="0" width="18" height="70" fill="#20c997" rx="2"/>
                                                <text x="35" y="77" font-size="8" text-anchor="middle">5XL</text>
                                            </svg>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Navigation Pills -->
    <section class="py-4 bg-light sticky-top" style="top: 76px; z-index: 1020;">
        <div class="container">
            <ul class="nav nav-pills justify-content-center" id="guideTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="size-tab" data-bs-toggle="pill" data-bs-target="#size-guide" type="button" role="tab">
                        <i class="bi bi-rulers me-2"></i>Size Guide
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="measurement-tab" data-bs-toggle="pill" data-bs-target="#measurement-guide" type="button" role="tab">
                        <i class="bi bi-diagram-3 me-2"></i>How to Measure
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="care-tab" data-bs-toggle="pill" data-bs-target="#care-guide" type="button" role="tab">
                        <i class="bi bi-heart me-2"></i>Product Care
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="calculator-tab" data-bs-toggle="pill" data-bs-target="#size-calculator" type="button" role="tab">
                        <i class="bi bi-calculator me-2"></i>Size Calculator
                    </button>
                </li>
            </ul>
        </div>
    </section>

    <!-- Tab Content -->
    <section class="py-5">
        <div class="container">
            <div class="tab-content" id="guideTabContent">
                
                <!-- Size Guide Tab -->
                <div class="tab-pane fade show active" id="size-guide" role="tabpanel">
                    <div class="guide-card">
                        <h2 class="text-center mb-4">
                            <i class="bi bi-rulers text-primary me-2"></i>Clothing Size Charts
                        </h2>
                        
                        <!-- Women's Sizes -->
                        <div class="row">
                            <div class="col-lg-6 mb-4">
                                <div class="size-chart">
                                    <div class="bg-primary text-white p-3">
                                        <h4 class="mb-0"><i class="bi bi-person-dress me-2"></i>Women's Sizes</h4>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-hover mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Size</th>
                                                    <th>Bust (inches)</th>
                                                    <th>Waist (inches)</th>
                                                    <th>Hip (inches)</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td><strong>XS</strong></td>
                                                    <td>32-34</td>
                                                    <td>24-26</td>
                                                    <td>34-36</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>S</strong></td>
                                                    <td>34-36</td>
                                                    <td>26-28</td>
                                                    <td>36-38</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>M</strong></td>
                                                    <td>36-38</td>
                                                    <td>28-30</td>
                                                    <td>38-40</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>L</strong></td>
                                                    <td>38-41</td>
                                                    <td>30-33</td>
                                                    <td>40-43</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>XL</strong></td>
                                                    <td>41-44</td>
                                                    <td>33-36</td>
                                                    <td>43-46</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>XXL</strong></td>
                                                    <td>44-47</td>
                                                    <td>36-39</td>
                                                    <td>46-49</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>3XL</strong></td>
                                                    <td>47-50</td>
                                                    <td>39-42</td>
                                                    <td>49-52</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>4XL</strong></td>
                                                    <td>50-53</td>
                                                    <td>42-45</td>
                                                    <td>52-55</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>5XL</strong></td>
                                                    <td>53-56</td>
                                                    <td>45-48</td>
                                                    <td>55-58</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <!-- Men's Sizes -->
                            <div class="col-lg-6 mb-4">
                                <div class="size-chart">
                                    <div class="bg-success text-white p-3">
                                        <h4 class="mb-0"><i class="bi bi-person me-2"></i>Men's Sizes</h4>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-hover mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Size</th>
                                                    <th>Chest (inches)</th>
                                                    <th>Waist (inches)</th>
                                                    <th>Hip (inches)</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td><strong>S</strong></td>
                                                    <td>34-36</td>
                                                    <td>28-30</td>
                                                    <td>34-36</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>M</strong></td>
                                                    <td>38-40</td>
                                                    <td>32-34</td>
                                                    <td>38-40</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>L</strong></td>
                                                    <td>42-44</td>
                                                    <td>36-38</td>
                                                    <td>42-44</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>XL</strong></td>
                                                    <td>46-48</td>
                                                    <td>40-42</td>
                                                    <td>46-48</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>XXL</strong></td>
                                                    <td>50-52</td>
                                                    <td>44-46</td>
                                                    <td>50-52</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>XXXL</strong></td>
                                                    <td>54-56</td>
                                                    <td>48-50</td>
                                                    <td>54-56</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>4XL</strong></td>
                                                    <td>58-60</td>
                                                    <td>52-54</td>
                                                    <td>58-60</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>5XL</strong></td>
                                                    <td>62-64</td>
                                                    <td>56-58</td>
                                                    <td>62-64</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Shoe Sizes -->
                        <div class="row mt-4">
                            <div class="col-lg-6 mb-4">
                                <div class="size-chart">
                                    <div class="bg-warning text-dark p-3">
                                        <h4 class="mb-0"><i class="bi bi-shoe me-2"></i>Women's Shoes</h4>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-hover mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>US Size</th>
                                                    <th>UK Size</th>
                                                    <th>EU Size</th>
                                                    <th>Foot Length (inches)</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr><td>5</td><td>3</td><td>35.5</td><td>8.5</td></tr>
                                                <tr><td>6</td><td>4</td><td>36.5</td><td>9.0</td></tr>
                                                <tr><td>7</td><td>5</td><td>37.5</td><td>9.5</td></tr>
                                                <tr><td>8</td><td>6</td><td>38.5</td><td>10.0</td></tr>
                                                <tr><td>9</td><td>7</td><td>39.5</td><td>10.5</td></tr>
                                                <tr><td>10</td><td>8</td><td>40.5</td><td>11.0</td></tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-6 mb-4">
                                <div class="size-chart">
                                    <div class="bg-info text-white p-3">
                                        <h4 class="mb-0"><i class="bi bi-shoe me-2"></i>Men's Shoes</h4>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-hover mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>US Size</th>
                                                    <th>UK Size</th>
                                                    <th>EU Size</th>
                                                    <th>Foot Length (inches)</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr><td>7</td><td>6</td><td>40</td><td>9.6</td></tr>
                                                <tr><td>8</td><td>7</td><td>41</td><td>10.0</td></tr>
                                                <tr><td>9</td><td>8</td><td>42</td><td>10.4</td></tr>
                                                <tr><td>10</td><td>9</td><td>43</td><td>10.8</td></tr>
                                                <tr><td>11</td><td>10</td><td>44</td><td>11.2</td></tr>
                                                <tr><td>12</td><td>11</td><td>45</td><td>11.6</td></tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Measurement Guide Tab -->
                <div class="tab-pane fade" id="measurement-guide" role="tabpanel">
                    <div class="guide-card">
                        <h2 class="text-center mb-4">
                            <i class="bi bi-diagram-3 text-primary me-2"></i>How to Measure Yourself
                        </h2>
                        
                        <div class="row">
                            <div class="col-lg-8">
                                <div class="measurement-step mb-4 d-flex">
                                    <div class="step-counter">1</div>
                                    <div>
                                        <h5>Chest/Bust Measurement</h5>
                                        <p class="text-muted">Measure around the fullest part of your chest/bust, keeping the tape measure level and snug but not tight.</p>
                                        <div class="alert alert-info">
                                            <i class="bi bi-info-circle me-2"></i>
                                            <strong>Tip:</strong> Wear a well-fitting bra for the most accurate bust measurement.
                                        </div>
                                    </div>
                                </div>

                                <div class="measurement-step mb-4 d-flex">
                                    <div class="step-counter">2</div>
                                    <div>
                                        <h5>Waist Measurement</h5>
                                        <p class="text-muted">Find your natural waistline (the narrowest part of your torso) and measure around it.</p>
                                        <div class="alert alert-warning">
                                            <i class="bi bi-exclamation-triangle me-2"></i>
                                            <strong>Note:</strong> Don't suck in your stomach - breathe naturally for an accurate measurement.
                                        </div>
                                    </div>
                                </div>

                                <div class="measurement-step mb-4 d-flex">
                                    <div class="step-counter">3</div>
                                    <div>
                                        <h5>Hip Measurement</h5>
                                        <p class="text-muted">Measure around the fullest part of your hips, typically about 7-9 inches below your waist.</p>
                                        <div class="alert alert-success">
                                            <i class="bi bi-check-circle me-2"></i>
                                            <strong>Pro tip:</strong> Stand with your feet together for the most accurate hip measurement.
                                        </div>
                                    </div>
                                </div>

                                <div class="measurement-step mb-4 d-flex">
                                    <div class="step-counter">4</div>
                                    <div>
                                        <h5>Foot Length Measurement</h5>
                                        <p class="text-muted">Place your foot on a piece of paper and mark the heel and longest toe. Measure the distance between the two marks.</p>
                                        <div class="alert alert-primary">
                                            <i class="bi bi-lightbulb me-2"></i>
                                            <strong>Best practice:</strong> Measure your feet in the evening when they're slightly swollen for the most comfortable fit.
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-lg-4">
                                <div class="measurement-tool floating-element">
                                    <!-- Measuring Tape SVG -->
                                    <div class="mb-3">
                                        <svg width="120" height="120" viewBox="0 0 120 120" style="max-width: 100%;">
                                            <!-- Measuring tape -->
                                            <ellipse cx="60" cy="60" rx="45" ry="45" fill="none" stroke="#ffc107" stroke-width="8" stroke-dasharray="5,3"/>
                                            <ellipse cx="60" cy="60" rx="35" ry="35" fill="none" stroke="#ffc107" stroke-width="4"/>
                                            
                                            <!-- Numbers on tape -->
                                            <text x="60" y="20" font-size="10" text-anchor="middle" fill="#333">0</text>
                                            <text x="95" y="35" font-size="10" text-anchor="middle" fill="#333">15</text>
                                            <text x="105" y="65" font-size="10" text-anchor="middle" fill="#333">30</text>
                                            <text x="95" y="95" font-size="10" text-anchor="middle" fill="#333">45</text>
                                            <text x="60" y="110" font-size="10" text-anchor="middle" fill="#333">60</text>
                                            <text x="25" y="95" font-size="10" text-anchor="middle" fill="#333">75</text>
                                            <text x="15" y="65" font-size="10" text-anchor="middle" fill="#333">90</text>
                                            <text x="25" y="35" font-size="10" text-anchor="middle" fill="#333">105</text>
                                            
                                            <!-- Center body silhouette -->
                                            <ellipse cx="60" cy="45" rx="8" ry="10" fill="#e3f2fd"/>
                                            <rect x="52" y="55" width="16" height="25" fill="#e3f2fd" rx="8"/>
                                            
                                            <!-- Measuring tape end -->
                                            <circle cx="95" cy="25" r="6" fill="#ffc107" stroke="#333" stroke-width="2"/>
                                            <rect x="92" y="22" width="6" height="6" fill="#333"/>
                                        </svg>
                                    </div>
                                    <h5>Measurement Tips</h5>
                                    <ul class="text-start list-unstyled">
                                        <li><i class="bi bi-check text-success me-2"></i>Use a flexible measuring tape</li>
                                        <li><i class="bi bi-check text-success me-2"></i>Wear fitted clothing or undergarments</li>
                                        <li><i class="bi bi-check text-success me-2"></i>Ask someone to help you measure</li>
                                        <li><i class="bi bi-check text-success me-2"></i>Take measurements in the morning</li>
                                        <li><i class="bi bi-check text-success me-2"></i>Record all measurements</li>
                                    </ul>
                                    
                                    <div class="mt-4">
                                        <button class="btn btn-primary" onclick="openMeasurementVideo()">
                                            <i class="bi bi-play-circle me-2"></i>Watch Video Guide
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Product Care Tab -->
                <div class="tab-pane fade" id="care-guide" role="tabpanel">
                    <div class="guide-card">
                        <h2 class="text-center mb-4">
                            <i class="bi bi-heart text-primary me-2"></i>Product Care Instructions
                        </h2>
                        
                        <div class="row">
                            <div class="col-lg-4">
                                <div class="care-instruction">
                                    <h5><i class="bi bi-droplet text-info me-2"></i>Washing</h5>
                                    <ul class="mb-0">
                                        <li>Machine wash cold (30°C max)</li>
                                        <li>Use mild detergent</li>
                                        <li>Wash similar colors together</li>
                                        <li>Turn garments inside out</li>
                                        <li>Use gentle cycle for delicates</li>
                                    </ul>
                                </div>
                                
                                <div class="care-instruction">
                                    <h5><i class="bi bi-sun text-warning me-2"></i>Drying</h5>
                                    <ul class="mb-0">
                                        <li>Air dry when possible</li>
                                        <li>Avoid direct sunlight</li>
                                        <li>Low heat tumble dry if needed</li>
                                        <li>Remove promptly to prevent wrinkles</li>
                                        <li>Lay flat for knit items</li>
                                    </ul>
                                </div>
                            </div>
                            
                            <div class="col-lg-4">
                                <div class="care-instruction">
                                    <h5><i class="bi bi-thermometer text-danger me-2"></i>Ironing</h5>
                                    <ul class="mb-0">
                                        <li>Check care label for temperature</li>
                                        <li>Iron inside out when possible</li>
                                        <li>Use pressing cloth for delicates</li>
                                        <li>Avoid over-ironing synthetic fabrics</li>
                                        <li>Steam iron for best results</li>
                                    </ul>
                                </div>
                                
                                <div class="care-instruction">
                                    <h5><i class="bi bi-archive text-secondary me-2"></i>Storage</h5>
                                    <ul class="mb-0">
                                        <li>Clean before storing</li>
                                        <li>Use padded hangers for jackets</li>
                                        <li>Fold knitwear to prevent stretching</li>
                                        <li>Store in cool, dry place</li>
                                        <li>Use cedar blocks for moth protection</li>
                                    </ul>
                                </div>
                            </div>
                            
                            <div class="col-lg-4">
                                <div class="care-instruction">
                                    <h5><i class="bi bi-exclamation-triangle text-warning me-2"></i>Special Care</h5>
                                    <ul class="mb-0">
                                        <li>Professional cleaning for suits</li>
                                        <li>Spot clean when possible</li>
                                        <li>Test cleaners on hidden areas</li>
                                        <li>Handle zippers and buttons gently</li>
                                        <li>Repair small tears immediately</li>
                                    </ul>
                                </div>
                                
                                <div class="care-instruction">
                                    <h5><i class="bi bi-shield-check text-success me-2"></i>Maintenance</h5>
                                    <ul class="mb-0">
                                        <li>Remove pills with fabric shaver</li>
                                        <li>Rotate wearing to extend life</li>
                                        <li>Treat stains immediately</li>
                                        <li>Follow manufacturer instructions</li>
                                        <li>Inspect regularly for damage</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Care Symbols -->
                        <div class="mt-5">
                            <h4 class="text-center mb-4">Care Symbol Guide</h4>
                            <div class="row text-center">
                                <div class="col-md-2 col-4 mb-3">
                                    <div class="p-3 border rounded">
                                        <i class="bi bi-droplet display-6 text-info"></i>
                                        <p class="small mt-2 mb-0">Machine Wash</p>
                                    </div>
                                </div>
                                <div class="col-md-2 col-4 mb-3">
                                    <div class="p-3 border rounded">
                                        <i class="bi bi-snow display-6 text-primary"></i>
                                        <p class="small mt-2 mb-0">Cold Water</p>
                                    </div>
                                </div>
                                <div class="col-md-2 col-4 mb-3">
                                    <div class="p-3 border rounded">
                                        <i class="bi bi-sun display-6 text-warning"></i>
                                        <p class="small mt-2 mb-0">Tumble Dry</p>
                                    </div>
                                </div>
                                <div class="col-md-2 col-4 mb-3">
                                    <div class="p-3 border rounded">
                                        <i class="bi bi-thermometer display-6 text-danger"></i>
                                        <p class="small mt-2 mb-0">Iron</p>
                                    </div>
                                </div>
                                <div class="col-md-2 col-4 mb-3">
                                    <div class="p-3 border rounded">
                                        <i class="bi bi-x-circle display-6 text-danger"></i>
                                        <p class="small mt-2 mb-0">Do Not Bleach</p>
                                    </div>
                                </div>
                                <div class="col-md-2 col-4 mb-3">
                                    <div class="p-3 border rounded">
                                        <i class="bi bi-circle display-6 text-dark"></i>
                                        <p class="small mt-2 mb-0">Dry Clean</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Size Calculator Tab -->
                <div class="tab-pane fade" id="size-calculator" role="tabpanel">
                    <div class="guide-card">
                        <h2 class="text-center mb-4">
                            <i class="bi bi-calculator text-primary me-2"></i>Size Calculator
                        </h2>
                        
                        <div class="row justify-content-center">
                            <div class="col-lg-8">
                                <div class="size-calculator">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="category" class="form-label">Category</label>
                                                <select class="form-select" id="category">
                                                    <option value="">Select Category</option>
                                                    <option value="women-clothing">Women's Clothing</option>
                                                    <option value="men-clothing">Men's Clothing</option>
                                                    <option value="women-shoes">Women's Shoes</option>
                                                    <option value="men-shoes">Men's Shoes</option>
                                                </select>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="bust-chest" class="form-label">Bust/Chest (inches)</label>
                                                <input type="number" class="form-control" id="bust-chest" placeholder="Enter measurement">
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="waist" class="form-label">Waist (inches)</label>
                                                <input type="number" class="form-control" id="waist" placeholder="Enter measurement">
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="hip" class="form-label">Hip (inches)</label>
                                                <input type="number" class="form-control" id="hip" placeholder="Enter measurement">
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="height" class="form-label">Height (inches)</label>
                                                <input type="number" class="form-control" id="height" placeholder="Enter height">
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="weight" class="form-label">Weight (lbs) - Optional</label>
                                                <input type="number" class="form-control" id="weight" placeholder="Enter weight">
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="text-center">
                                        <button class="btn btn-primary btn-lg" onclick="calculateSize()">
                                            <i class="bi bi-calculator me-2"></i>Calculate My Size
                                        </button>
                                    </div>
                                    
                                    <div id="size-result" class="mt-4" style="display: none;">
                                        <div class="alert alert-success text-center">
                                            <h5><i class="bi bi-check-circle me-2"></i>Recommended Size</h5>
                                            <div class="display-4 text-primary" id="recommended-size">M</div>
                                            <p class="mt-2">Based on your measurements, we recommend size <strong id="size-text">Medium</strong></p>
                                            
                                            <!-- Size confidence indicator -->
                                            <div class="mt-3">
                                                <small class="text-muted d-block">Confidence Level:</small>
                                                <div class="progress mx-auto" style="width: 200px; height: 8px;">
                                                    <div class="progress-bar bg-success" id="confidence-bar" role="progressbar" style="width: 85%"></div>
                                                </div>
                                                <small class="text-success" id="confidence-text">85% Match</small>
                                            </div>
                                            
                                            <!-- Big size info -->
                                            <div class="mt-3" id="big-size-info" style="display: none;">
                                                <div class="alert alert-info mb-2">
                                                    <i class="bi bi-info-circle me-2"></i>
                                                    <strong>Plus Size Fit Tips:</strong> Consider ordering one size up for a more comfortable fit, especially for stretchy fabrics.
                                                </div>
                                            </div>
                                            
                                            <small class="text-muted">This is a general recommendation. Please check the specific product's size chart for the best fit.</small>
                                        </div>

                                        <!-- Alternative sizes suggestion -->
                                        <div class="text-center mt-3">
                                            <h6>You might also consider:</h6>
                                            <div class="d-flex justify-content-center gap-2 flex-wrap" id="alternative-sizes">
                                                <!-- Will be populated by JavaScript -->
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-light py-5">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-4">
                    <h5 class="mb-3">
                        <i class="bi bi-shop me-2"></i>Your Company
                    </h5>
                    <p class="text-muted">
                        Find your perfect fit with our comprehensive size guide and keep your 
                        products looking great with our care instructions.
                    </p>
                </div>
                <div class="col-lg-2 col-md-6">
                    <h6 class="mb-3">Guides</h6>
                    <ul class="list-unstyled">
                        <li><a href="{{ route('pages.size.guide') }}" class="text-muted text-decoration-none">Size Guide</a></li>
                        <li><a href="{{ route('pages.product.care') }}" class="text-muted text-decoration-none">Product Care</a></li>
                        <li><a href="#size-calculator" class="text-muted text-decoration-none">Size Calculator</a></li>
                        <li><a href="#measurement-guide" class="text-muted text-decoration-none">How to Measure</a></li>
                    </ul>
                </div>
                <div class="col-lg-2 col-md-6">
                    <h6 class="mb-3">Support</h6>
                    <ul class="list-unstyled">
                        <li><a href="{{ route('pages.help') }}" class="text-muted text-decoration-none">Help Center</a></li>
                        <li><a href="{{ route('pages.faq') }}" class="text-muted text-decoration-none">FAQ</a></li>
                        <li><a href="#" class="text-muted text-decoration-none">Size Questions</a></li>
                        <li><a href="#" class="text-muted text-decoration-none">Fit Guarantee</a></li>
                    </ul>
                </div>
                <div class="col-lg-4">
                    <h6 class="mb-3">Need Help Finding Your Size?</h6>
                    <div class="text-muted">
                        <p>Our fit experts are here to help!</p>
                        <button class="btn btn-outline-light btn-sm">
                            <i class="bi bi-chat-dots me-2"></i>Chat with Fit Expert
                        </button>
                    </div>
                </div>
            </div>
            <hr class="my-4">
            <div class="text-center text-muted">
                <p>&copy; {{ date('Y') }} Your Company. All rights reserved. | Perfect Fit Guaranteed</p>
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5 JS CDN -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JavaScript -->
    <script>
        // Animate cards on scroll
        const observeElements = () => {
            const cards = document.querySelectorAll('.guide-card');
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('animate');
                    }
                });
            }, {
                threshold: 0.1,
                rootMargin: '50px'
            });

            cards.forEach(card => observer.observe(card));
        };

        // Size calculator function
        function calculateSize() {
            const category = document.getElementById('category').value;
            const bust = parseFloat(document.getElementById('bust-chest').value);
            const waist = parseFloat(document.getElementById('waist').value);
            const hip = parseFloat(document.getElementById('hip').value);

            if (!category || !bust || !waist || !hip) {
                alert('Please fill in all required measurements and select a category.');
                return;
            }

            let recommendedSize = 'M';
            let sizeText = 'Medium';

            // Enhanced size calculation logic with extended sizes
            if (category === 'women-clothing') {
                if (bust <= 34 && waist <= 26 && hip <= 36) {
                    recommendedSize = 'XS';
                    sizeText = 'Extra Small';
                } else if (bust <= 36 && waist <= 28 && hip <= 38) {
                    recommendedSize = 'S';
                    sizeText = 'Small';
                } else if (bust <= 38 && waist <= 30 && hip <= 40) {
                    recommendedSize = 'M';
                    sizeText = 'Medium';
                } else if (bust <= 41 && waist <= 33 && hip <= 43) {
                    recommendedSize = 'L';
                    sizeText = 'Large';
                } else if (bust <= 44 && waist <= 36 && hip <= 46) {
                    recommendedSize = 'XL';
                    sizeText = 'Extra Large';
                } else if (bust <= 47 && waist <= 39 && hip <= 49) {
                    recommendedSize = 'XXL';
                    sizeText = '2X Large';
                } else if (bust <= 50 && waist <= 42 && hip <= 52) {
                    recommendedSize = '3XL';
                    sizeText = '3X Large';
                } else if (bust <= 53 && waist <= 45 && hip <= 55) {
                    recommendedSize = '4XL';
                    sizeText = '4X Large';
                } else {
                    recommendedSize = '5XL';
                    sizeText = '5X Large';
                }
            } else if (category === 'men-clothing') {
                if (bust <= 36 && waist <= 30) {
                    recommendedSize = 'S';
                    sizeText = 'Small';
                } else if (bust <= 40 && waist <= 34) {
                    recommendedSize = 'M';
                    sizeText = 'Medium';
                } else if (bust <= 44 && waist <= 38) {
                    recommendedSize = 'L';
                    sizeText = 'Large';
                } else if (bust <= 48 && waist <= 42) {
                    recommendedSize = 'XL';
                    sizeText = 'Extra Large';
                } else if (bust <= 52 && waist <= 46) {
                    recommendedSize = 'XXL';
                    sizeText = '2X Large';
                } else if (bust <= 56 && waist <= 50) {
                    recommendedSize = 'XXXL';
                    sizeText = '3X Large';
                } else if (bust <= 60 && waist <= 54) {
                    recommendedSize = '4XL';
                    sizeText = '4X Large';
                } else {
                    recommendedSize = '5XL';
                    sizeText = '5X Large';
                }
            }

            // Show result with animation
            document.getElementById('recommended-size').textContent = recommendedSize;
            document.getElementById('size-text').textContent = sizeText;
            
            // Calculate confidence level based on measurements
            let confidence = 85; // Base confidence
            const avgMeasurement = (bust + waist + hip) / 3;
            
            // Adjust confidence based on measurement consistency
            const variation = Math.max(Math.abs(bust - avgMeasurement), Math.abs(waist - avgMeasurement), Math.abs(hip - avgMeasurement));
            confidence = Math.max(65, confidence - (variation * 2));
            
            document.getElementById('confidence-bar').style.width = confidence + '%';
            document.getElementById('confidence-text').textContent = Math.round(confidence) + '% Match';
            
            // Show big size info for larger sizes
            const bigSizes = ['XXL', '3XL', '4XL', '5XL'];
            const bigSizeInfo = document.getElementById('big-size-info');
            if (bigSizes.includes(recommendedSize)) {
                bigSizeInfo.style.display = 'block';
            } else {
                bigSizeInfo.style.display = 'none';
            }
            
            // Generate alternative size suggestions
            const sizeOrder = ['XS', 'S', 'M', 'L', 'XL', 'XXL', '3XL', '4XL', '5XL'];
            const currentIndex = sizeOrder.indexOf(recommendedSize);
            const alternatives = document.getElementById('alternative-sizes');
            alternatives.innerHTML = '';
            
            // Add one size down and one size up if they exist
            if (currentIndex > 0) {
                const smallerSize = sizeOrder[currentIndex - 1];
                alternatives.innerHTML += `<span class="badge bg-light text-dark border">${smallerSize} (Tighter fit)</span>`;
            }
            if (currentIndex < sizeOrder.length - 1) {
                const largerSize = sizeOrder[currentIndex + 1];
                alternatives.innerHTML += `<span class="badge bg-light text-dark border">${largerSize} (Looser fit)</span>`;
            }
            
            const resultDiv = document.getElementById('size-result');
            resultDiv.style.display = 'block';
            resultDiv.style.animation = 'fadeIn 0.5s ease';
        }

        // Open measurement video (placeholder function)
        function openMeasurementVideo() {
            alert('Measurement video tutorial will be available soon!');
        }

        // Initialize animations when DOM is loaded
        document.addEventListener('DOMContentLoaded', function() {
            observeElements();
            
            // Add smooth scrolling to anchor links
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) {
                        target.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                });
            });
        });

        // Tab change animation
        const triggerTabList = [].slice.call(document.querySelectorAll('#guideTabs button'));
        triggerTabList.forEach(function (triggerEl) {
            const tabTrigger = new bootstrap.Tab(triggerEl);
            
            triggerEl.addEventListener('click', function (event) {
                event.preventDefault();
                tabTrigger.show();
            });
        });
    </script>
</body>
</html>