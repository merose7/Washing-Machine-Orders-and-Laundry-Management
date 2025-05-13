<!DOCTYPE html>

<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto+Condensed:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
  
    <title>The Daily Wash</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <!-- Bootstrap JS -->
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
  
    <!-- Other stylesheets -->
    <link rel="stylesheet" href="{{ asset('css/fontawesome.css') }}">
    <link rel="stylesheet" href="{{ asset('css/templatemo-digimedia-v3.css') }}">
    <link rel="stylesheet" href="{{ asset('css/animated.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/dailywash-style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/dailywash-responsive.css') }}">
    <link href="{{ asset('css/home.css') }}" rel="stylesheet">

  </head>
  <script>
    window.addEventListener('load', function () {
      const preloader = document.getElementById('js-preloader');
      const body = document.querySelector('.fade-in');
  
      setTimeout(() => {
        preloader.style.opacity = '0';
        preloader.style.transition = 'opacity 0.3s ease';
  
        setTimeout(() => {
          preloader.style.display = 'none';
          body.classList.add('show'); // tampilkan isi dengan fade-in
        }, 300); 
      }, 1000); 
    });
  </script>
  

<body>
  
  <!-- ***** Preloader Start ***** -->
  <div id="js-preloader" class="js-preloader">
    <div class="preloader-inner">
      <span class="dot"></span>
      <div class="dots">
        <span></span>
        <span></span>
        <span></span>
      </div>
    </div>
  </div>

  <!-- ***** Header Area Start ***** -->
  <header class="header-area header-sticky wow slideInDown" data-wow-duration="0.75s" data-wow-delay="0s">
    <div class="container">
      <div class="row">
        <div class="col-12">
          <nav class="main-nav">
            <!-- ***** Logo Start ***** -->
            <a href="#top" class="logo">
              <img src="{{ asset('images\Logo_The_Daily_Wash-removebg-preview.png') }}" alt="Logo" style="height: 70px; width: auto;"> 
            </a>
            <!-- ***** Logo End ***** -->
            <!-- ***** Menu Start ***** -->
            <ul class="nav">
              <li class="scroll-to-section"><a href="#home">Home</a></li>
              <li class="scroll-to-section"><a href="#mesin-cuci">Booking</a></li>
              <li class="scroll-to-section"><a href="#review">Reviews</a></li>
              <li class="scroll-to-section"><a href="#contact">Location</a></li>
              <li class="scroll-to-section">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-link border-0 p-0 m-0 align-baseline">Logout</button>
                </form>
              </li>
</ul>
            <!-- ***** Menu End ***** -->
          </nav>
        </div>
      </div>
    </div>
  </header>

  <!-- ***** Header Area End ***** -->
  </a>
  <a href="https://wa.me/+6282110000000">
  <button type="button" class="floating-btn1 btn btn-success" data-toggle="modal" data-target="#staticBackdrop">
    <image src="{{ asset('images/whatsapp.svg') }}" alt="Logo" style="height: 20px; width: 20;">
  </button>
  </a>

  <div class="main-banner wow fadeIn" id="home" data-wow-duration="1s" data-wow-delay="0.5s">
  <div class="container">
    <div class="row">
      <div class="col-lg-12">
        <div class="row">
          <div class="col-lg-6 align-self-center">
            <div class="left-content show-up header-text wow fadeInLeft" data-wow-duration="1s" data-wow-delay="1s">
              <div class="row">
                <div class="col-lg-16">
                  <h6>The Daily Wash Indonesia</h6>
                  <h2>Your Laundry is Our Duty</h2>
                    <h3>Your Destination after Holiday</h3>
                  <h4>Come and Wash Your Clothes</h4>
                </div>
                <div class="col-lg-6 mt-3">
                  <div class="border-first-button scroll-to-section">
                    <a href="#mesin-cuci">Booking Now</a>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-lg-6">
            <div class="right-image wow fadeInRight" data-wow-duration="1s" data-wow-delay="0.5s">
              <img src="{{ asset('images\Logo_The_Daily_Wash-removebg-preview.png') }}" alt="dec" style="height: 350px; width: auto;">
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- List Mesin Cuci untuk Pelanggan -->
<section id="mesin-cuci" class="our-portfolio section py-5">
  <div class="container">
    <div class="row justify-content-center mb-4">
      <div class="col-lg-8 text-center">
        <div class="section-heading">
          <h2 class="mb-3">Pilih Mesin Cuci Anda di <em>The Daily Wash Indonesia</em></h2>
          <p class="text-muted">Booking mesin cuci dengan mudah dan cepat!</p>
        </div>
      </div>
    </div>

    <div class="container mt-5">
      <div class="row">
          @foreach($machines as $machine)
              <div class="col-md-4 mb-3">
                  <div class="card h-100">
                      <div class="card-body">
                          <h5 class="card-title">{{ $machine->name }}</h5>
                          <p>Status: 
                              @if($machine->status == 'available')
                                  <span class="badge bg-success">Tersedia</span>
                              @elseif($machine->status == 'booked')
                                  <span class="badge bg-warning text-dark">Dibooking</span>
                              @else
                                  <span class="badge bg-danger">Maintenance</span>
                              @endif
                          </p>
                          @if($machine->status == 'available')
                              <a href="{{ route('booking.create', ['machine_id' => $machine->id]) }}" class="btn btn-primary">Booking Sekarang</a>
                          @else
                              <button class="btn btn-secondary" disabled>Tidak Tersedia</button>
                          @endif
                      </div>
                  </div>
              </div>
          @endforeach
      </div>
  </div>
  
  
<!-- Daftar Ulasan Pelanggan-->
<section id="review">
<div class="container mt-5">
  <div class="row">
    <div class="col-lg-5">
      <div class="section-heading wow fadeInLeft" data-wow-duration="1s" data-wow-delay="0.3s">
        <h4>Ulasan 
            <em>Pelanggan</em></h4>
        <div class="line-dec"></div>
    </div>
    </div>
  </div>

  <div class="mt-4 overflow-auto" style="white-space: nowrap;">
    <!-- Card wrapper -->
    <div class="d-inline-flex" style="gap: 16px; padding-bottom: 10px;">

      <!-- Card 1 -->
      <div class="card review-card" style="min-width: 250px;">
        <div class="card-body">
          <h5 class="card-title">Rina S.</h5>
          <p class="card-text text-muted">📝 Pelayanannya cepat dan mesin cucinya bersih. Harga terjangkau!</p>
          <div style="color: #f0ad4e;">Rating: 5/5 ⭐</div>
          <p><strong>Komentar:</strong> Sangat puas, pasti kembali lagi!</p>
        </div>
      </div>

      <!-- Card 2 -->
      <div class="card review-card" style="min-width: 250px;">
        <div class="card-body">
          <h5 class="card-title">Andi Pratama</h5>
          <p class="card-text text-muted">📝 Tempatnya nyaman, booking online gampang.</p>
          <div style="color: #f0ad4e;">Rating: 4/5 ⭐</div>
          <p><strong>Komentar:</strong> Tambah mesin lagi ya saat weekend!</p>
        </div>
      </div>

      <!-- Card 3 -->
      <div class="card review-card" style="min-width: 250px;">
        <div class="card-body">
          <h5 class="card-title">Dewi Lestari</h5>
          <p class="card-text text-muted">📝 Reminder booking via email sangat membantu.</p>
          <div style="color: #f0ad4e;">Rating: 5/5 ⭐</div>
          <p><strong>Komentar:</strong> Layanan modern, cocok buat mahasiswa!</p>
        </div>
      </div>

      <!-- Card 4 -->
      <div class="card review-card" style="min-width: 250px;">
        <div class="card-body">
          <h5 class="card-title">Budi Santoso</h5>
          <p class="card-text text-muted">📝 Mesin cuci bersih, cepat kering!</p>
          <div style="color: #f0ad4e;">Rating: 5/5 ⭐</div>
          <p><strong>Komentar:</strong> Proses laundry jadi hemat waktu.</p>
        </div>
      </div>

      <!-- Card 5-->
      <div class="card review-card" style="min-width: 250px;">
        <div class="card-body">
          <h5 class="card-title">Melati Anggraeni</h5>
          <p class="card-text text-muted">📝 Booking mesin bisa pilih waktu, fleksibel!</p>
          <div style="color: #f0ad4e;">Rating: 5/5 ⭐</div>
          <p><strong>Komentar:</strong> Fitur bookingnya keren banget!</p>
        </div>
      </div>

    </div>
  </div>
</div>
</section>

<!-- Contact Us Section -->
<div id="contact" class="contact-us section">
    <div class="container">
      <div class="row">
        <div class="col-lg-6 offset-lg-3 text-center">
          <div class="section-heading wow fadeIn" data-wow-duration="1s" data-wow-delay="0.5s">
            <h6>Location</h6>
            <h4>Get In Touch With Us <em>Now</em></h4>
            <div class="line-dec"></div>
          </div>
        </div>
      </div>
  
      <div class="row justify-content-center">
        
        <!-- Google Maps -->
        <div class="col-lg-10">
          <div class="map-container shadow rounded overflow-hidden">
            <iframe 
              src="https://maps.google.com/maps?q=Ketintang,Surabaya&t=&z=13&ie=UTF8&iwloc=&output=embed" 
              width="100%" 
              height="450" 
              style="border:0;" 
              allowfullscreen 
              loading="lazy">
            </iframe>
          </div>
        </div>
      </div>
  
    </div>
  </div>

<!-- Contact Information & Footer -->
<footer class="footer">
    <div class="container p-4">
        <div class="row">
            <!-- Tentang Laundry -->
            <div class="col-lg-6 col-md-12 mb-4">
                <h6 class="mb-3 footer-title">The Daily Wash Indonesia</h6>
                <p class="footer-text">
                    Your Laundry is Our Duty.<br>Your Destination after Holiday.
                </p>
            </div>

            <!-- Contact -->
            <div class="col-lg-3 col-md-6 mb-4">
                <h6 class="mb-3 footer-title">Contact</h6>
                <ul class="list-unstyled mb-0">
                    <li class="mb-1">
                        <a href="#!" class="footer-link">Jl. Ketintang Baru III No.50c, Surabaya</a>
                    </li>
                    <li class="mb-1">
                        <a href="mailto:contact@dailywash.com" class="footer-link">contact@dailywash.com</a>
                    </li>
                    <li class="mb-1">
                        <a href="tel:+6281234567890" class="footer-link">+62 812-3456-7890</a>
                    </li>
                    <li>
                        <a href="#!" class="footer-link">Where we deliver?</a>
                    </li>
                </ul>
            </div>

            <!-- Opening Hours -->
            <div class="col-lg-3 col-md-6 mb-4">
                <h6 class="mb-3 footer-title">Opening Hours</h6>
                <table class="footer-table">
                    <tbody>
                        <tr>
                            <td>Mon - Fri:</td>
                            <td>8am - 9pm</td>
                        </tr>
                        <tr>
                            <td>Sat - Sun:</td>
                            <td>8am - 1am</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Copyright -->
    <div class="text-center p-3 footer-copyright">
        © 2025 Copyright:
        <a class="footer-link" href="http://127.0.0.1:8000/laundryhome">The Daily Wash Indonesia.com</a>
    </div>
</footer>


  <!-- Scripts -->
  <script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
  <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
  <script src="{{ asset('assets/js/owl-carousel.js') }}"></script>
  <script src="{{ asset('assets/js/animation.js') }}"></script>
  <script src="{{ asset('assets/js/imagesloaded.js') }}"></script>
  <script src="{{ asset('assets/js/custom.js') }}"></script>
  <script src="{{ asset('js/sweetalert.min.js') }}"></script>
</body>
</html>


