gdprCookieNotice({
    locale: 'es', //This is the default value
    timeout: 500, //Time until the cookie bar appears
    expiration: 30, //This is the default value, in days
    domain: '.nold.io', //If you run the same cookie notice on all subdomains, define the main domain starting with a .
    implicit: true, //Accept cookies on scroll
    statement: 'https://google.com', //Link to your cookie statement page
    performance: ['JSESSIONID'], //Cookies in the performance category.
    analytics: ['ga'], //Cookies in the analytics category.
    //marketing: ['SSID'] //Cookies in the marketing category.
});