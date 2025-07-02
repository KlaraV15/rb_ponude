import { BrowserRouter as Router, Routes, Route } from 'react-router-dom';
import Login from './pages/Login';
import Register from './pages/Register';
import Home from './pages/Home';
import Proizvodi from './pages/Proizvodi';
import Slanje from './pages/Slanje';
import Profil from './pages/Profil';

function App() {



  return (
    <>
      <Router>
        <Routes>
          <Route path="/login" element={<Login />} />
          <Route path="/register" element={<Register />} />
          <Route path="/" element={<Home />} />
          <Route path="/proizvodi" element={<Proizvodi />} />
          <Route path="/slanje" element={<Slanje />} />
          <Route path="/profil" element={<Profil />} />
        </Routes>
      </Router>

    </>
  )
}

export default App
