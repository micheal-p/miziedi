<?php
namespace Miziedi\Controllers;

class PageController {
    
    public function returns() {
        view('pages/simple_page', [
            'pageTitle' => 'Returns & Exchanges',
            'heading' => 'Returns Policy',
            'content' => 'We offer free returns within 30 days of purchase. Items must be unworn and tags attached. To initiate a return, please contact support@miziedi.com with your order number.'
        ]);
    }

    public function story() {
        view('pages/simple_page', [
            'pageTitle' => 'Our Story',
            'heading' => 'The Miziedi Story',
            'content' => 'Founded in 2023, Miziedi was born from a passion for the outdoors and premium fashion. We believe in gear that looks good in the city and performs on the mountain.'
        ]);
    }

    public function sustainability() {
        view('pages/simple_page', [
            'pageTitle' => 'Sustainability',
            'heading' => 'Eco-Friendly Promise',
            'content' => 'We are committed to reducing our footprint. 80% of our materials are recycled or ethically sourced. We are working towards 100% carbon neutrality by 2030.'
        ]);
    }
    
    public function newArrivals() {
        // Redirect to homepage sorted by newest (logic handled in ProductController if you add sorting later)
        header('Location: /'); 
        exit;
    }
    
    public function bestSellers() {
        // Redirect to homepage for now
        header('Location: /');
        exit;
    }
}
