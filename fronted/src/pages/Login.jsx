import React, { useState } from "react";
import axios from "axios";
import { useNavigate } from "react-router-dom";
import '../App.css';

function Login() {
    const [email, setEmail] = useState('');
    const [lozinka, setLozinka] = useState('');
    const [error, setError] = useState('');
    const [loading, setLoading] = useState(false);
    const navigate = useNavigate();

    const handleSubmit = async (e) => {
        e.preventDefault();
        setError('');
        setLoading(true);

        console.log("Submitting:", { email, lozinka }); // Debug 1: Check input values

        try {
            const response = await axios.post(
                "http://localhost/rb_ponude/backend/api/Login.php",
                { email, lozinka },
                {
                    headers: { "Content-Type": "application/json" },
                    withCredentials: true // Add this for CORS
                }
            );

            console.log("Server Response:", response.data); // Debug 2: Check server response

            if (response.data.success) {
                localStorage.setItem("user", JSON.stringify(response.data.user));
                navigate("/home");
            } else {
                setError(response.data.message || "Pogrešan email ili lozinka.");
            }
        } catch (err) {
            console.error("Error Details:", err); // Debug 3: Full error logging
            console.error("Response Data:", err.response?.data); // Debug 4: Server error response
            setError("Greška prilikom povezivanja na server.");
        } finally {
            setLoading(false);
        }
    };

    return (
        <form onSubmit={handleSubmit} className="login-container">
            <h2>Prijava</h2>
            {error && <p className="error-message">{error}</p>}

            <div className="form-group">
                <label>Email</label>
                <input
                    type="email"
                    value={email}
                    onChange={(e) => setEmail(e.target.value)}
                    placeholder="Unesi email"
                />
            </div>

            <div className="form-group">
                <label>Lozinka</label>
                <input
                    type="password"
                    value={lozinka}
                    onChange={(e) => setLozinka(e.target.value)}
                    placeholder="Unesi lozinku"
                />
            </div>

            <button type="submit" disabled={loading}>
                {loading ? "Prijavljivanje..." : "Prijavi se"}
            </button>
        </form>
    );
}

export default Login;
