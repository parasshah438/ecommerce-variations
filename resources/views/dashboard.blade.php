@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-tachometer-alt me-2"></i>
                        Dashboard
                    </h4>
                </div>

                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <h5>Welcome back, {{ Auth::user()->name }}!</h5>
                            <p class="text-muted">You are successfully logged in to your account.</p>
                            
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle me-2"></i>
                                <strong>Login Successful!</strong> You have successfully authenticated using OTP.
                            </div>

                            <div class="row mt-4">
                                <div class="col-md-4">
                                    <div class="card bg-light">
                                        <div class="card-body text-center">
                                            <i class="fas fa-user fa-2x text-primary mb-2"></i>
                                            <h6>Profile</h6>
                                            <small class="text-muted">Manage your account</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card bg-light">
                                        <div class="card-body text-center">
                                            <i class="fas fa-shopping-cart fa-2x text-success mb-2"></i>
                                            <h6>Orders</h6>
                                            <small class="text-muted">View your orders</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card bg-light">
                                        <div class="card-body text-center">
                                            <i class="fas fa-heart fa-2x text-danger mb-2"></i>
                                            <h6>Wishlist</h6>
                                            <small class="text-muted">Saved items</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="card border-primary">
                                <div class="card-header bg-primary text-white">
                                    <h6 class="mb-0">Account Information</h6>
                                </div>
                                <div class="card-body">
                                    <p><strong>Name:</strong> {{ Auth::user()->name }}</p>
                                    <p><strong>Email:</strong> {{ Auth::user()->email }}</p>
                                    @if(Auth::user()->mobile)
                                        <p><strong>Mobile:</strong> {{ Auth::user()->mobile }}</p>
                                    @endif
                                    <p><strong>Member Since:</strong> {{ Auth::user()->created_at->format('M Y') }}</p>
                                    
                                    <hr>
                                    
                                    <div class="d-grid gap-2">
                                        <a href="{{ route('products.index') }}" class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-shopping-bag me-2"></i>
                                            Continue Shopping
                                        </a>
                                        
                                        <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-secondary btn-sm w-100">
                                                <i class="fas fa-sign-out-alt me-2"></i>
                                                Logout
                                            </button>
                                        </form>
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
@endsection
