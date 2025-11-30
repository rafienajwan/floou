# 🚀 Quick Start Guide for Frontend Developers

Panduan cepat untuk frontend developer menggunakan Floou Backend API.

## 📋 Prerequisites

1. Backend server sudah running di `http://localhost:8000`
2. Database sudah di-seed dengan data dummy
3. Storage link sudah dibuat

## 🔑 Authentication Flow

### 1. Setup Axios (Recommended)

```javascript
// api.js
import axios from 'axios';

const api = axios.create({
  baseURL: 'http://localhost:8000/api',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  }
});

// Add token to all requests
api.interceptors.request.use((config) => {
  const token = localStorage.getItem('token');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

// Handle 401 errors globally
api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      localStorage.removeItem('token');
      window.location.href = '/login';
    }
    return Promise.reject(error);
  }
);

export default api;
```

### 2. Login Flow

```javascript
// Login
const login = async (email, password) => {
  try {
    const response = await api.post('/login', { email, password });
    
    // Save token
    localStorage.setItem('token', response.data.token);
    
    // Save user data
    localStorage.setItem('user', JSON.stringify(response.data.user));
    
    return response.data.user;
  } catch (error) {
    throw error.response?.data || error;
  }
};

// Example usage
try {
  const user = await login('admin@floou.com', 'password123');
  console.log('Logged in:', user);
  // Redirect to dashboard
} catch (error) {
  console.error('Login failed:', error.message);
}
```

### 3. Register Flow

```javascript
const register = async (userData) => {
  try {
    const response = await api.post('/register', {
      name: userData.name,
      email: userData.email,
      password: userData.password,
      password_confirmation: userData.password_confirmation,
      phone: userData.phone,  // optional
      address: userData.address  // optional
    });
    
    localStorage.setItem('token', response.data.token);
    localStorage.setItem('user', JSON.stringify(response.data.user));
    
    return response.data.user;
  } catch (error) {
    // error.response.data.errors contains validation errors
    throw error.response?.data || error;
  }
};
```

### 4. Logout Flow

```javascript
const logout = async () => {
  try {
    await api.post('/logout');
  } catch (error) {
    console.error('Logout error:', error);
  } finally {
    localStorage.removeItem('token');
    localStorage.removeItem('user');
    window.location.href = '/login';
  }
};
```

## 🌱 Working with Plants

### 1. Fetch Plants with Filters

```javascript
const getPlants = async (filters = {}) => {
  try {
    const response = await api.get('/plants', {
      params: {
        search: filters.search || '',
        category_id: filters.categoryId || '',
        plant_type_id: filters.plantTypeId || '',
        min_price: filters.minPrice || '',
        max_price: filters.maxPrice || '',
        in_stock: filters.inStockOnly ? 1 : '',
        sort_by: filters.sortBy || 'created_at',
        sort_order: filters.sortOrder || 'desc',
        per_page: filters.perPage || 12,
        page: filters.page || 1
      }
    });
    
    return response.data.plants;
  } catch (error) {
    throw error;
  }
};

// Example usage
const plants = await getPlants({
  search: 'monstera',
  categoryId: 1,
  inStockOnly: true,
  sortBy: 'price',
  sortOrder: 'asc',
  perPage: 12,
  page: 1
});

// Access data
console.log('Plants:', plants.data);
console.log('Total:', plants.total);
console.log('Current Page:', plants.current_page);
console.log('Last Page:', plants.last_page);
```

### 2. Get Single Plant Detail

```javascript
const getPlantDetail = async (plantId) => {
  try {
    const response = await api.get(`/plants/${plantId}`);
    return response.data.plant;
  } catch (error) {
    throw error;
  }
};

// Usage
const plant = await getPlantDetail(1);
console.log('Plant:', plant.name);
console.log('Price:', plant.price);
console.log('Image URL:', plant.image_url);
console.log('Average Rating:', plant.average_rating);
console.log('Reviews Count:', plant.reviews_count);
console.log('Stock:', plant.stock);
```

### 3. Display Plant Card (React Example)

```jsx
const PlantCard = ({ plant }) => {
  const formatPrice = (price) => {
    return new Intl.NumberFormat('id-ID', {
      style: 'currency',
      currency: 'IDR',
      minimumFractionDigits: 0
    }).format(price);
  };

  return (
    <div className="plant-card">
      <img 
        src={plant.image_url || '/placeholder.jpg'} 
        alt={plant.name}
        onError={(e) => e.target.src = '/placeholder.jpg'}
      />
      <h3>{plant.name}</h3>
      <p className="price">{formatPrice(plant.price)}</p>
      <div className="rating">
        ⭐ {plant.average_rating} ({plant.reviews_count} reviews)
      </div>
      <div className="stock">
        {plant.stock > 0 ? (
          <span className="in-stock">Stock: {plant.stock}</span>
        ) : (
          <span className="out-of-stock">Out of Stock</span>
        )}
      </div>
      <button 
        disabled={plant.stock === 0}
        onClick={() => addToCart(plant)}
      >
        Add to Cart
      </button>
    </div>
  );
};
```

## 🛒 Shopping Cart & Orders

### 1. Create Order

```javascript
const createOrder = async (cartItems, shippingInfo) => {
  try {
    const orderData = {
      items: cartItems.map(item => ({
        plant_id: item.id,
        quantity: item.quantity
      })),
      recipient_name: shippingInfo.name,
      recipient_phone: shippingInfo.phone,
      shipping_address: shippingInfo.address,
      shipping_method: shippingInfo.shippingMethod, // 'standard' or 'express'
      notes: shippingInfo.notes || ''
    };
    
    const response = await api.post('/orders', orderData);
    return response.data.order;
  } catch (error) {
    // Check for stock errors
    if (error.response?.status === 400) {
      alert(error.response.data.message);
    }
    throw error;
  }
};

// Example usage
const cartItems = [
  { id: 1, name: 'Monstera', quantity: 2, price: 150000 },
  { id: 3, name: 'Snake Plant', quantity: 1, price: 75000 }
];

const shippingInfo = {
  name: 'John Doe',
  phone: '081234567890',
  address: 'Jl. Example No. 123, Jakarta Selatan',
  shippingMethod: 'standard', // standard = Rp 20.000, express = Rp 50.000
  notes: 'Tolong kirim pagi'
};

try {
  const order = await createOrder(cartItems, shippingInfo);
  console.log('Order created:', order.invoice_number);
  // Clear cart and redirect to order confirmation
} catch (error) {
  console.error('Order failed:', error);
}
```

### 2. Get User Orders

```javascript
const getUserOrders = async () => {
  try {
    const response = await api.get('/orders');
    return response.data.orders.data;
  } catch (error) {
    throw error;
  }
};

// Display orders
const orders = await getUserOrders();
orders.forEach(order => {
  console.log(`
    Invoice: ${order.invoice_number}
    Status: ${order.status}
    Total: ${order.grand_total}
    Items: ${order.order_details.length}
  `);
});
```

### 3. Cancel Order

```javascript
const cancelOrder = async (orderId, reason) => {
  try {
    const response = await api.post(`/orders/${orderId}/cancel`, {
      reason: reason
    });
    return response.data.order;
  } catch (error) {
    // Order can only be canceled if status is 'pending' or 'confirmed'
    alert(error.response?.data?.message || 'Cannot cancel order');
    throw error;
  }
};

// Usage
try {
  await cancelOrder(1, 'Salah beli produk');
  alert('Order canceled successfully');
} catch (error) {
  console.error('Cancel failed:', error);
}
```

### 4. Order Status Display

```javascript
const getOrderStatusBadge = (status) => {
  const statusConfig = {
    pending: {
      label: 'Menunggu Konfirmasi',
      color: 'yellow',
      icon: '⏳',
      canCancel: true
    },
    confirmed: {
      label: 'Dikonfirmasi',
      color: 'blue',
      icon: '✓',
      canCancel: true
    },
    completed: {
      label: 'Selesai',
      color: 'green',
      icon: '✓✓',
      canReview: true
    },
    canceled: {
      label: 'Dibatalkan',
      color: 'red',
      icon: '✗'
    }
  };
  
  return statusConfig[status] || statusConfig.pending;
};

// Usage in React
const OrderStatus = ({ order }) => {
  const status = getOrderStatusBadge(order.status);
  
  return (
    <div>
      <span className={`badge badge-${status.color}`}>
        {status.icon} {status.label}
      </span>
      
      {status.canCancel && (
        <button onClick={() => cancelOrder(order.id)}>
          Cancel Order
        </button>
      )}
      
      {status.canReview && (
        <button onClick={() => reviewOrder(order.id)}>
          Write Review
        </button>
      )}
    </div>
  );
};
```

## ⭐ Reviews

### 1. Create Review

```javascript
const createReview = async (plantId, orderId, rating, comment) => {
  try {
    const response = await api.post('/reviews', {
      plant_id: plantId,
      order_id: orderId,
      rating: rating, // 1-5
      comment: comment
    });
    return response.data.review;
  } catch (error) {
    // Check requirements
    const message = error.response?.data?.message;
    if (message) alert(message);
    throw error;
  }
};

// Usage
try {
  const review = await createReview(
    1, // plant_id
    5, // order_id (must be completed order)
    5, // rating
    'Tanaman sampai dengan selamat, sangat bagus!'
  );
  alert('Review submitted successfully');
} catch (error) {
  console.error('Review failed:', error);
}
```

### 2. Display Reviews

```javascript
const getPlantReviews = async (plantId, page = 1) => {
  try {
    const response = await api.get(`/plants/${plantId}/reviews`, {
      params: { page }
    });
    return response.data.reviews;
  } catch (error) {
    throw error;
  }
};

// React component example
const ReviewsList = ({ plantId }) => {
  const [reviews, setReviews] = useState([]);
  
  useEffect(() => {
    getPlantReviews(plantId).then(data => {
      setReviews(data.data);
    });
  }, [plantId]);
  
  return (
    <div className="reviews">
      {reviews.map(review => (
        <div key={review.id} className="review">
          <div className="rating">
            {'⭐'.repeat(review.rating)}
          </div>
          <p className="comment">{review.comment}</p>
          <p className="author">
            by {review.user.name} - {new Date(review.created_at).toLocaleDateString()}
          </p>
        </div>
      ))}
    </div>
  );
};
```

## 🔔 Notifications

### 1. Get Notifications

```javascript
const getNotifications = async () => {
  try {
    const response = await api.get('/notifications');
    return {
      notifications: response.data.notifications.data,
      unreadCount: response.data.unread_count
    };
  } catch (error) {
    throw error;
  }
};

// Usage
const { notifications, unreadCount } = await getNotifications();
console.log(`You have ${unreadCount} unread notifications`);
```

### 2. Mark as Read

```javascript
const markNotificationAsRead = async (notificationId) => {
  try {
    await api.patch(`/notifications/${notificationId}/read`);
  } catch (error) {
    console.error('Failed to mark as read:', error);
  }
};

// Mark all as read
const markAllAsRead = async () => {
  try {
    await api.patch('/notifications/read-all');
  } catch (error) {
    console.error('Failed to mark all as read:', error);
  }
};
```

### 3. Notification Bell Component (React)

```jsx
const NotificationBell = () => {
  const [notifications, setNotifications] = useState([]);
  const [unreadCount, setUnreadCount] = useState(0);
  const [isOpen, setIsOpen] = useState(false);
  
  useEffect(() => {
    fetchNotifications();
    // Poll every 30 seconds
    const interval = setInterval(fetchNotifications, 30000);
    return () => clearInterval(interval);
  }, []);
  
  const fetchNotifications = async () => {
    try {
      const data = await getNotifications();
      setNotifications(data.notifications);
      setUnreadCount(data.unreadCount);
    } catch (error) {
      console.error('Failed to fetch notifications:', error);
    }
  };
  
  const handleMarkAsRead = async (id) => {
    await markNotificationAsRead(id);
    fetchNotifications();
  };
  
  return (
    <div className="notification-bell">
      <button onClick={() => setIsOpen(!isOpen)}>
        🔔
        {unreadCount > 0 && (
          <span className="badge">{unreadCount}</span>
        )}
      </button>
      
      {isOpen && (
        <div className="notifications-dropdown">
          {notifications.length === 0 ? (
            <p>No notifications</p>
          ) : (
            notifications.map(notif => (
              <div 
                key={notif.id}
                className={notif.is_read ? 'read' : 'unread'}
                onClick={() => handleMarkAsRead(notif.id)}
              >
                <p>{notif.message}</p>
                <small>{new Date(notif.created_at).toLocaleString()}</small>
              </div>
            ))
          )}
          
          {unreadCount > 0 && (
            <button onClick={markAllAsRead}>
              Mark all as read
            </button>
          )}
        </div>
      )}
    </div>
  );
};
```

## 🎨 Utility Functions

### 1. Price Formatting

```javascript
export const formatPrice = (price) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0
  }).format(price);
};

// Usage
console.log(formatPrice(150000)); // "Rp 150.000"
```

### 2. Date Formatting

```javascript
export const formatDate = (dateString) => {
  const date = new Date(dateString);
  return date.toLocaleDateString('id-ID', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  });
};

// Usage
console.log(formatDate('2025-11-30T10:00:00.000000Z')); // "30 November 2025"
```

### 3. Image Error Handler

```javascript
export const handleImageError = (e) => {
  e.target.src = '/placeholder.jpg';
  e.target.onerror = null; // Prevent infinite loop
};

// Usage in JSX
<img 
  src={plant.image_url} 
  alt={plant.name}
  onError={handleImageError}
/>
```

## 🔒 Admin Features

### Check if User is Admin

```javascript
const isAdmin = () => {
  const user = JSON.parse(localStorage.getItem('user'));
  return user?.role === 'admin';
};

// Usage
if (isAdmin()) {
  // Show admin features
}
```

### Admin Dashboard

```javascript
const getDashboardStats = async () => {
  try {
    const response = await api.get('/dashboard');
    return response.data;
  } catch (error) {
    if (error.response?.status === 403) {
      alert('Access denied. Admin only.');
    }
    throw error;
  }
};

// Usage
const stats = await getDashboardStats();
console.log('Total Users:', stats.statistics.total_users);
console.log('Total Plants:', stats.statistics.total_plants);
console.log('Total Orders:', stats.statistics.total_orders);
console.log('Low Stock Plants:', stats.low_stock_plants);
```

## 🐛 Error Handling

### Global Error Handler

```javascript
const handleApiError = (error) => {
  if (error.response) {
    // Server responded with error
    const status = error.response.status;
    const data = error.response.data;
    
    switch (status) {
      case 400:
        return data.message || 'Bad request';
      case 401:
        return 'Unauthenticated. Please login.';
      case 403:
        return 'Access denied.';
      case 404:
        return 'Resource not found.';
      case 422:
        // Validation errors
        const errors = data.errors;
        return Object.values(errors).flat().join(', ');
      case 500:
        return 'Server error. Please try again later.';
      default:
        return data.message || 'An error occurred';
    }
  } else if (error.request) {
    // Request made but no response
    return 'Network error. Please check your connection.';
  } else {
    // Something else happened
    return error.message || 'An error occurred';
  }
};

// Usage
try {
  await createOrder(orderData);
} catch (error) {
  const message = handleApiError(error);
  alert(message);
}
```

## 📱 Complete Example: Product Listing Page (React)

```jsx
import { useState, useEffect } from 'react';
import api from './api';

const ProductsPage = () => {
  const [plants, setPlants] = useState([]);
  const [loading, setLoading] = useState(true);
  const [filters, setFilters] = useState({
    search: '',
    categoryId: '',
    sortBy: 'created_at',
    sortOrder: 'desc',
    page: 1
  });
  const [pagination, setPagination] = useState({});
  
  useEffect(() => {
    fetchPlants();
  }, [filters]);
  
  const fetchPlants = async () => {
    setLoading(true);
    try {
      const response = await api.get('/plants', { params: filters });
      setPlants(response.data.plants.data);
      setPagination({
        currentPage: response.data.plants.current_page,
        lastPage: response.data.plants.last_page,
        total: response.data.plants.total
      });
    } catch (error) {
      console.error('Failed to fetch plants:', error);
    } finally {
      setLoading(false);
    }
  };
  
  const handleSearch = (e) => {
    setFilters({ ...filters, search: e.target.value, page: 1 });
  };
  
  const handlePageChange = (page) => {
    setFilters({ ...filters, page });
  };
  
  if (loading) return <div>Loading...</div>;
  
  return (
    <div className="products-page">
      <input
        type="search"
        placeholder="Search plants..."
        value={filters.search}
        onChange={handleSearch}
      />
      
      <div className="products-grid">
        {plants.map(plant => (
          <div key={plant.id} className="product-card">
            <img src={plant.image_url} alt={plant.name} />
            <h3>{plant.name}</h3>
            <p>{formatPrice(plant.price)}</p>
            <div>⭐ {plant.average_rating} ({plant.reviews_count})</div>
            <button>Add to Cart</button>
          </div>
        ))}
      </div>
      
      <div className="pagination">
        {Array.from({ length: pagination.lastPage }, (_, i) => i + 1).map(page => (
          <button
            key={page}
            onClick={() => handlePageChange(page)}
            className={page === pagination.currentPage ? 'active' : ''}
          >
            {page}
          </button>
        ))}
      </div>
    </div>
  );
};
```

## 🚀 Next Steps

1. **Setup CORS**: Pastikan backend sudah configure CORS untuk domain frontend Anda
2. **Environment Variables**: Simpan `baseURL` di environment variable
3. **Error Tracking**: Implement error tracking (Sentry, etc.)
4. **Loading States**: Tambahkan loading indicators untuk UX yang lebih baik
5. **Caching**: Consider using React Query atau SWR untuk caching
6. **Optimistic Updates**: Update UI immediately, then sync with server

## 📚 Additional Resources

- [Full API Documentation](API_DOCUMENTATION.md)
- [Postman Collection](Floou_API.postman_collection.json)
- Backend README untuk setup instructions

---

**Happy Coding! 🚀**
