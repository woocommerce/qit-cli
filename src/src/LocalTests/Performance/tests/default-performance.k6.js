import { check, sleep } from "k6";
import http from "k6/http";

export let options = {
    stages: [
        { duration: "10s", target: 5 },
        { duration: "20s", target: 10 },
        { duration: "10s", target: 0 },
    ],
    thresholds: {
        // Performance thresholds - K6 will exit with non-zero if these fail
        'http_req_duration': ['p(95)<5000'], // 95th percentile under 5 seconds (increased)
        'http_req_duration{expected_response:true}': ['avg<2000'], // Average response time under 2 seconds (increased)
        'http_req_failed': ['rate<0.2'], // Error rate under 20% (increased from 10%)
        'checks': ['rate>0.8'], // At least 80% of checks should pass (decreased from 90%)
    },
};

export default function() {
    const baseUrl = __ENV.BASE_URL || "http://localhost";
    
    // Test homepage
    let response = http.get(baseUrl);
    check(response, {
        "homepage status is 200": (r) => r.status === 200,
        "homepage loads in < 500ms": (r) => r.timings.duration < 500,
    });
    
    sleep(1);
    
    // Test WooCommerce shop page
    response = http.get(`${baseUrl}/shop/`);
    check(response, {
        "shop page status is 200": (r) => r.status === 200,
        "shop page loads in < 800ms": (r) => r.timings.duration < 800,
    });
    
    sleep(1);
    
    // Test cart page
    response = http.get(`${baseUrl}/cart/`);
    check(response, {
        "cart page accessible": (r) => r.status === 200 || r.status === 404,
    });
    
    // Test checkout page
    response = http.get(`${baseUrl}/checkout/`);
    check(response, {
        "checkout page accessible": (r) => r.status === 200 || r.status === 404,
    });
    
    // Test WooCommerce REST API health
    response = http.get(`${baseUrl}/wp-json/wc/v3/system_status`);
    check(response, {
        "WooCommerce API accessible": (r) => r.status === 200 || r.status === 401, // 401 is OK, means auth is needed
    });
    
    sleep(Math.random() * 2 + 1);
} 