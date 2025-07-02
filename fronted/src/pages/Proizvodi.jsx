import React, { useState, useEffect } from 'react';
import axios from 'axios';
import '../App.css';

function Proizvodi() {
    const [products, setProducts] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);

    const fetchProducts = async () => {
        try {
            setLoading(true);
            setError(null);

            const response = await axios.get(
                'http://localhost/rb_ponude/backend/api/getProducts.php',
                {
                    headers: {
                        'Accept': 'application/json',
                    },
                    timeout: 10000
                }
            );

            if (response.data.success) {
                setProducts(response.data.data);
            } else {
                setError(response.data.message || 'Došlo je do greške pri učitavanju proizvoda');
            }
        } catch (err) {
            console.error('API Error:', err);
            let errorMessage = 'Greška u komunikaciji sa serverom';

            if (err.response) {
                errorMessage = err.response.data?.message ||
                    `HTTP ${err.response.status}: ${err.response.statusText}`;
            } else if (err.request) {
                errorMessage = 'Server nije odgovorio';
            }

            setError(errorMessage);
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        fetchProducts();
    }, []);

    if (loading) {
        return (
            <div className="loading-screen">
                <div className="spinner"></div>
                <p>Učitavanje proizvoda...</p>
            </div>
        );
    }

    if (error) {
        return (
            <div className="error-screen">
                <h3>Greška</h3>
                <p>{error}</p>
                <button onClick={fetchProducts}>Pokušaj ponovo</button>
            </div>
        );
    }

    return (
        <div className="products-container">
            <h2>Naši proizvodi</h2>
            <div className="products-grid">
                {products.map(product => (
                    <div key={product.id} className="product-card">
                        <img
                            src={`http://localhost/rb_ponude/uploads/${product.slika_putanja}`}
                            alt={product.naziv}
                        />
                        <h3>{product.naziv}</h3>
                        <p>{product.cijena} HRK</p>
                    </div>
                ))}
            </div>
        </div>
    );
}

export default Proizvodi;