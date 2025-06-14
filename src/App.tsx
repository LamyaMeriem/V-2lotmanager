import React from 'react';
import { BrowserRouter as Router, Routes, Route } from 'react-router-dom';
import Layout from './components/Layout';
import Dashboard from './pages/Dashboard';
import LotsList from './pages/LotsList';
import LotDetail from './pages/LotDetail';
import CreateLot from './pages/CreateLot';
import ImportMapping from './pages/ImportMapping';
import ProductQualification from './pages/ProductQualification';
import Statistics from './pages/Statistics';
import Configuration from './pages/Configuration';

function App() {
  return (
    <Router>
      <Layout>
        <Routes>
          <Route path="/" element={<Dashboard />} />
          <Route path="/lots" element={<LotsList />} />
          <Route path="/lots/:id" element={<LotDetail />} />
          <Route path="/lots/:id/qualification" element={<ProductQualification />} />
          <Route path="/create-lot" element={<CreateLot />} />
          <Route path="/import-mapping" element={<ImportMapping />} />
          <Route path="/statistics" element={<Statistics />} />
          <Route path="/configuration" element={<Configuration />} />
        </Routes>
      </Layout>
    </Router>
  );
}

export default App;