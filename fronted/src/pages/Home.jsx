import React, { useState, useRef } from "react";
import axios from "axios";
import Webcam from "react-webcam";

function Home() {
    const [naziv, setNaziv] = useState("");
    const [cijena, setCijena] = useState("");
    const [selectedFile, setSelectedFile] = useState(null);
    const [capturedImage, setCapturedImage] = useState(null);
    const [loading, setLoading] = useState(false);
    const [message, setMessage] = useState("");
    const webcamRef = useRef(null);

    const capture = () => {
        const imageSrc = webcamRef.current.getScreenshot();
        setCapturedImage(imageSrc);
        setSelectedFile(null);
    };

    const handleFileChange = (e) => {
        setSelectedFile(e.target.files[0]);
        setCapturedImage(null);
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        setMessage("");
        setLoading(true);

        try {
            const formData = new FormData();
            formData.append("naziv", naziv);
            formData.append("cijena", cijena);

            if (selectedFile) {
                formData.append("image", selectedFile);
            } else if (capturedImage) {
                const blob = await fetch(capturedImage).then(res => res.blob());
                formData.append("image", blob, "capture.jpg");
            }

            const response = await axios.post(
                "http://localhost/rb_ponude/backend/api/uploadProduct.php",
                formData,
                {
                    headers: {
                        "Content-Type": "multipart/form-data",
                    },
                    // Removed withCredentials unless you need cookies/sessions
                }
            );

            if (response.data.success) {
                setMessage("Uspješno dodano!");
                setNaziv("");
                setCijena("");
                setSelectedFile(null);
                setCapturedImage(null);
            } else {
                setMessage(response.data.message || "Greška pri dodavanju");
            }
        } catch (error) {
            console.error("Full error:", error);
            const errorMsg = error.response?.data?.message ||
                error.response?.data?.error ||
                "Greška u komunikaciji sa serverom";
            setMessage(errorMsg);
        } finally {
            setLoading(false);
        }
    };

    return (
        <div className="home-container">
            <h2>Dodaj proizvod</h2>
            <form onSubmit={handleSubmit}>
                <div>
                    <label>Naziv:</label>
                    <input
                        type="text"
                        value={naziv}
                        onChange={(e) => setNaziv(e.target.value)}
                        required
                    />
                </div>
                <div>
                    <label>Cijena:</label>
                    <input
                        type="number"
                        value={cijena}
                        onChange={(e) => setCijena(e.target.value)}
                        required
                        min="0"
                        step="0.01"
                    />
                </div>

                <div>
                    <label>Odaberi sliku iz galerije:</label>
                    <input type="file" accept="image/*" onChange={handleFileChange} />
                </div>

                <div>
                    <p>ILI</p>
                    <Webcam
                        audio={false}
                        height={240}
                        ref={webcamRef}
                        screenshotFormat="image/jpeg"
                        width={320}
                        videoConstraints={{ facingMode: "user" }}
                    />
                    <button type="button" onClick={capture}>
                        Uslikaj se
                    </button>
                </div>

                {capturedImage && (
                    <div>
                        <h4>Pregled slike sa kamere:</h4>
                        <img
                            src={capturedImage}
                            alt="captured"
                            style={{ width: "320px", height: "240px" }}
                        />
                    </div>
                )}

                <button type="submit" disabled={loading}>
                    {loading ? "Šaljem..." : "Pošalji proizvod"}
                </button>
            </form>
            {message && <p className={message.includes("Uspješno") ? "success" : "error"}>{message}</p>}
        </div>
    );
}

export default Home;