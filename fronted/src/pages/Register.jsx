import React, { useState } from "react";
import axios from "axios";
import '../App.css';

function Register() {
    const [formData, setFormData] = useState({
        ime: '',
        email: '',
        lozinka: '',
        confirmLozinka: ''
    });

    const [errors, setErrors] = useState({});
    const [success, setSuccess] = useState(false);
    const [loading, setLoading] = useState(false);

    const handleChange = (e) => {
        const { name, value } = e.target;
        setFormData(prev => ({ ...prev, [name]: value }));
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        setErrors({});
        setLoading(true);

        // Validacija
        const newErrors = {};
        if (!formData.ime.trim()) newErrors.ime = 'Ime je obavezno';
        if (!formData.email.trim()) newErrors.email = 'Email je obavezan';
        else if (!/^\S+@\S+\.\S+$/.test(formData.email)) newErrors.email = 'Neispravan email';
        if (!formData.lozinka) newErrors.lozinka = 'Lozinka je obavezna';
        else if (formData.lozinka.length < 6) newErrors.lozinka = 'Lozinka mora imati barem 6 znakova';
        if (formData.lozinka !== formData.confirmLozinka) newErrors.confirmLozinka = 'Lozinke se ne podudaraju';

        if (Object.keys(newErrors).length > 0) {
            setErrors(newErrors);
            setLoading(false);
            return;
        }

        try {
            const response = await axios.post(
                "http://localhost/rb_ponude/backend/api/Register.php",
                {
                    ime: formData.ime,
                    email: formData.email,
                    lozinka: formData.lozinka,
                },
                {
                    headers: {
                        "Content-Type": "application/json",
                    },
                }
            );

            if (response.data.success) {
                setSuccess(true);
            } else {
                setErrors({ api: response.data.message || "Registracija nije uspjela." });
            }
        } catch (err) {
            setErrors({ api: "Greška prilikom spajanja na server." });
        } finally {
            setLoading(false);
        }
    };

    if (success) {
        return (
            <div className="register-container">
                <h2>Registracija uspješna!</h2>
                <p>Možete se sada prijaviti.</p>
            </div>
        );
    }

    return (
        <form onSubmit={handleSubmit} className="register-container">
            <h2>Registracija</h2>
            {errors.api && <p className="error-message">{errors.api}</p>}

            <div className="form-group">
                <label>Ime</label>
                <input
                    type="text"
                    name="ime"
                    value={formData.ime}
                    onChange={handleChange}
                />
                {errors.ime && <span className="error-message">{errors.ime}</span>}
            </div>

            <div className="form-group">
                <label>Email</label>
                <input
                    type="email"
                    name="email"
                    value={formData.email}
                    onChange={handleChange}
                />
                {errors.email && <span className="error-message">{errors.email}</span>}
            </div>

            <div className="form-group">
                <label>Lozinka</label>
                <input
                    type="password"
                    name="lozinka"
                    value={formData.lozinka}
                    onChange={handleChange}
                />
                {errors.lozinka && <span className="error-message">{errors.lozinka}</span>}
            </div>

            <div className="form-group">
                <label>Potvrdi lozinku</label>
                <input
                    type="password"
                    name="confirmLozinka"
                    value={formData.confirmLozinka}
                    onChange={handleChange}
                />
                {errors.confirmLozinka && <span className="error-message">{errors.confirmLozinka}</span>}
            </div>

            <button type="submit" disabled={loading}>
                {loading ? "Registriram..." : "Registriraj se"}
            </button>
        </form>
    );
}

export default Register;
