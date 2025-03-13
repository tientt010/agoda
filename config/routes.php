<?php

/**
 * Định nghĩa tất cả routes và middleware
 */

// Define routes
$routes = [
    '/' => 'HomeController@index',
    '/login' => 'AuthController@login',
    '/register' => 'AuthController@register',
    '/logout' => 'AuthController@logout',
    '/forgot-password' => 'AuthController@forgotPassword',
    '/user/profile' => 'UserController@profile',
    '/user/edit-profile' => 'UserController@editProfile',
    '/user/change-password' => 'UserController@changePassword',
    '/user/update-profile' => 'UserController@updateProfile',
    '/user/update-password' => 'UserController@updatePassword',
    '/user/update-avatar' => 'UserController@updateAvatar',
    '/booking/(\d+)' => 'BookingController@create',
    '/hotels/(\d+)' => 'HotelController@show',
    '/hotels' => 'HotelController@index',

    // Thêm routes cho admin
    '/admin' => 'AdminController@dashboard',
    '/admin/hotels' => 'AdminController@hotels',
    '/admin/users' => 'AdminController@users',
    '/admin/bookings' => 'AdminController@bookings'
];

// Define middleware
$middlewares = [
    // Các route yêu cầu đăng nhập
    '/user/profile' => 'AuthMiddleware',
    '/user/edit-profile' => 'AuthMiddleware',
    '/user/change-password' => 'AuthMiddleware',
    '/user/update-profile' => 'AuthMiddleware',
    '/user/update-password' => 'AuthMiddleware',

    // Các route admin yêu cầu quyền admin
    '/admin' => ['AuthMiddleware', 'AdminMiddleware'],
    '/admin/hotels' => ['AuthMiddleware', 'AdminMiddleware'],
    '/admin/users' => ['AuthMiddleware', 'AdminMiddleware'],
    '/admin/bookings' => ['AuthMiddleware', 'AdminMiddleware']
];
