import React from 'react';
import { useParams, Link } from 'react-router-dom';
import { 
  ArrowLeft, 
  Edit, 
  Calendar,
  DollarSign,
  Package,
  User,
  CheckCircle,
  XCircle,
  AlertTriangle,
  BarChart3,
  FileText,
  History
} from 'lucide-react';

const LotDetail = () => {
  const { id } = useParams();

  // Simulated lot data
  const lot = {
    id: id || 'LOT-2025-01-15-001',
    name: 'Achat Fournisseur SYA - 15/01/2025',
    supplier: 'SYA Electronics',
    status: 'En cours',
    date: '2025-01-15',
    totalProducts: 45,
    processedProducts: 23,
    functionalProducts: 19,
    defectiveProducts: 4,
    costTotal: 12450,
    estimatedValue: 18675,
    margin: 6225,
    marginPercentage: 33.4
  };

  const products = [
    {
      id: 1,
      name: 'iPhone 11 64GB Black Grade B',
      serialNumber: '351234567890123',
      status: 'functional',
      sku: 'IPHONE-11-64-BLACK-B',
      costPrice: 450,
      salePrice: 549,
      margin: 99
    },
    {
      id: 2,
      name: 'Samsung Galaxy S20 128GB Blue',
      serialNumber: '351987654321098',
      status: 'pending',
      sku: '',
      costPrice: 380,
      salePrice: 0,
      margin: 0
    },
    {
      id: 3,
      name: 'iPhone 12 Pro 256GB Space Gray',
      serialNumber: '351456789012345',
      status: 'functional',
      sku: 'IPHONE-12-PRO-256-GRAY-A',
      costPrice: 680,
      salePrice: 899,
      margin: 219
    },
    {
      id: 4,
      name: 'Samsung Galaxy S21 écran cassé',
      serialNumber: '351789012345678',
      status: 'defective',
      sku: '',
      costPrice: 200,
      salePrice: 0,
      margin: -200,
      defects: ['Écran cassé']
    }
  ];

  const auditLog = [
    {
      id: 1,
      action: 'Création du lot',
      user: 'Admin',
      timestamp: '2025-01-15 09:00:00',
      details: 'Import de 45 produits depuis fichier Excel'
    },
    {
      id: 2,
      action: 'Qualification produit',
      user: 'Technicien A',
      timestamp: '2025-01-15 10:30:00',
      details: 'iPhone 11 (351234567890123) - Status: Fonctionnel'
    },
    {
      id: 3,
      action: 'Attribution SKU',
      user: 'Technicien A',
      timestamp: '2025-01-15 10:31:00',
      details: 'iPhone 11 (351234567890123) - SKU: IPHONE-11-64-BLACK-B'
    }
  ];

  const getStatusIcon = (status: string) => {
    switch (status) {
      case 'functional':
        return <CheckCircle className="w-4 h-4 text-green-600" />;
      case 'defective':
        return <XCircle className="w-4 h-4 text-red-600" />;
      default:
        return <AlertTriangle className="w-4 h-4 text-orange-600" />;
    }
  };

  const getStatusColor = (status: string) => {
    switch (status) {
      case 'functional':
        return 'bg-green-100 text-green-800';
      case 'defective':
        return 'bg-red-100 text-red-800';
      default:
        return 'bg-orange-100 text-orange-800';
    }
  };

  const getStatusText = (status: string) => {
    switch (status) {
      case 'functional':
        return 'Fonctionnel';
      case 'defective':
        return 'Défectueux';
      default:
        return 'En attente';
    }
  };

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="flex items-center justify-between">
        <div className="flex items-center space-x-4">
          <Link
            to="/lots"
            className="p-2 hover:bg-gray-100 rounded-lg transition-colors"
          >
            <ArrowLeft className="w-5 h-5 text-gray-600" />
          </Link>
          <div>
            <h1 className="text-2xl font-bold text-gray-900">{lot.name}</h1>
            <p className="text-gray-600">ID: {lot.id}</p>
          </div>
        </div>
        <div className="flex space-x-3">
          <Link
            to={`/lots/${lot.id}/qualification`}
            className="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium transition-colors flex items-center"
          >
            <Edit className="w-4 h-4 mr-2" />
            Traiter Produits
          </Link>
        </div>
      </div>

      {/* Lot Info Cards */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div className="bg-white p-6 rounded-xl shadow-sm border border-gray-200">
          <div className="flex items-center justify-between">
            <div>
              <p className="text-sm font-medium text-gray-600">Fournisseur</p>
              <p className="text-lg font-semibold text-gray-900 mt-1">{lot.supplier}</p>
            </div>
            <User className="w-8 h-8 text-blue-600" />
          </div>
        </div>

        <div className="bg-white p-6 rounded-xl shadow-sm border border-gray-200">
          <div className="flex items-center justify-between">
            <div>
              <p className="text-sm font-medium text-gray-600">Date d'import</p>
              <p className="text-lg font-semibold text-gray-900 mt-1">
                {new Date(lot.date).toLocaleDateString('fr-FR')}
              </p>
            </div>
            <Calendar className="w-8 h-8 text-green-600" />
          </div>
        </div>

        <div className="bg-white p-6 rounded-xl shadow-sm border border-gray-200">
          <div className="flex items-center justify-between">
            <div>
              <p className="text-sm font-medium text-gray-600">Produits</p>
              <p className="text-lg font-semibold text-gray-900 mt-1">
                {lot.processedProducts}/{lot.totalProducts}
              </p>
              <div className="w-full bg-gray-200 rounded-full h-1.5 mt-2">
                <div
                  className="bg-blue-600 h-1.5 rounded-full"
                  style={{ width: `${(lot.processedProducts / lot.totalProducts) * 100}%` }}
                ></div>
              </div>
            </div>
            <Package className="w-8 h-8 text-purple-600" />
          </div>
        </div>

        <div className="bg-white p-6 rounded-xl shadow-sm border border-gray-200">
          <div className="flex items-center justify-between">
            <div>
              <p className="text-sm font-medium text-gray-600">Marge Prévisionnelle</p>
              <p className="text-lg font-semibold text-green-600 mt-1">
                €{lot.margin.toLocaleString('fr-FR')}
              </p>
              <p className="text-sm text-gray-500">({lot.marginPercentage}%)</p>
            </div>
            <BarChart3 className="w-8 h-8 text-green-600" />
          </div>
        </div>
      </div>

      {/* Financial Summary */}
      <div className="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h2 className="text-lg font-semibold text-gray-900 mb-4">Résumé Financier</h2>
        <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
          <div className="text-center p-4 bg-red-50 rounded-lg">
            <DollarSign className="w-8 h-8 text-red-600 mx-auto mb-2" />
            <p className="text-2xl font-bold text-red-600">€{lot.costTotal.toLocaleString('fr-FR')}</p>
            <p className="text-sm text-gray-600">Coût d'achat total</p>
          </div>
          <div className="text-center p-4 bg-blue-50 rounded-lg">
            <DollarSign className="w-8 h-8 text-blue-600 mx-auto mb-2" />
            <p className="text-2xl font-bold text-blue-600">€{lot.estimatedValue.toLocaleString('fr-FR')}</p>
            <p className="text-sm text-gray-600">Valeur de revente estimée</p>
          </div>
          <div className="text-center p-4 bg-green-50 rounded-lg">
            <BarChart3 className="w-8 h-8 text-green-600 mx-auto mb-2" />
            <p className="text-2xl font-bold text-green-600">€{lot.margin.toLocaleString('fr-FR')}</p>
            <p className="text-sm text-gray-600">Marge brute ({lot.marginPercentage}%)</p>
          </div>
        </div>
      </div>

      {/* Products and Activity Tabs */}
      <div className="bg-white rounded-xl shadow-sm border border-gray-200">
        <div className="border-b border-gray-200">
          <nav className="flex space-x-8 px-6" aria-label="Tabs">
            <button className="border-b-2 border-blue-500 py-4 px-1 text-sm font-medium text-blue-600">
              Produits ({lot.totalProducts})
            </button>
            <button className="border-b-2 border-transparent py-4 px-1 text-sm font-medium text-gray-500 hover:text-gray-700">
              Historique
            </button>
          </nav>
        </div>

        <div className="p-6">
          {/* Products Table */}
          <div className="overflow-x-auto">
            <table className="min-w-full divide-y divide-gray-200">
              <thead>
                <tr>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Produit
                  </th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Statut
                  </th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    SKU
                  </th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Prix
                  </th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Marge
                  </th>
                </tr>
              </thead>
              <tbody className="bg-white divide-y divide-gray-200">
                {products.map((product) => (
                  <tr key={product.id} className="hover:bg-gray-50">
                    <td className="px-6 py-4 whitespace-nowrap">
                      <div>
                        <p className="text-sm font-medium text-gray-900">{product.name}</p>
                        <p className="text-sm text-gray-500">{product.serialNumber}</p>
                        {product.defects && product.defects.length > 0 && (
                          <div className="mt-1">
                            {product.defects.map((defect) => (
                              <span key={defect} className="inline-block bg-red-100 text-red-800 text-xs px-2 py-1 rounded mr-1">
                                {defect}
                              </span>
                            ))}
                          </div>
                        )}
                      </div>
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap">
                      <div className="flex items-center">
                        {getStatusIcon(product.status)}
                        <span className={`ml-2 px-2 py-1 text-xs font-medium rounded-full ${getStatusColor(product.status)}`}>
                          {getStatusText(product.status)}
                        </span>
                      </div>
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                      {product.sku || '-'}
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                      <div>
                        <p className="text-red-600">Achat: €{product.costPrice}</p>
                        {product.salePrice > 0 && (
                          <p className="text-green-600">Vente: €{product.salePrice}</p>
                        )}
                      </div>
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap text-sm">
                      {product.margin !== 0 ? (
                        <span className={`font-medium ${product.margin > 0 ? 'text-green-600' : 'text-red-600'}`}>
                          €{product.margin}
                        </span>
                      ) : (
                        <span className="text-gray-400">-</span>
                      )}
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </div>
      </div>

      {/* Audit Log Section (Hidden by default, would be shown when Historique tab is selected) */}
      <div className="bg-white rounded-xl shadow-sm border border-gray-200 p-6" style={{ display: 'none' }}>
        <h3 className="text-lg font-semibold text-gray-900 mb-4 flex items-center">
          <History className="w-5 h-5 mr-2" />
          Journal d'Audit
        </h3>
        <div className="space-y-4">
          {auditLog.map((entry) => (
            <div key={entry.id} className="flex items-start space-x-3 p-3 bg-gray-50 rounded-lg">
              <div className="w-2 h-2 bg-blue-600 rounded-full mt-2"></div>
              <div className="flex-1">
                <div className="flex items-center justify-between">
                  <p className="font-medium text-gray-900">{entry.action}</p>
                  <span className="text-sm text-gray-500">
                    {new Date(entry.timestamp).toLocaleString('fr-FR')}
                  </span>
                </div>
                <p className="text-sm text-gray-600 mt-1">{entry.details}</p>
                <p className="text-xs text-gray-500 mt-1">Par: {entry.user}</p>
              </div>
            </div>
          ))}
        </div>
      </div>
    </div>
  );
};

export default LotDetail;