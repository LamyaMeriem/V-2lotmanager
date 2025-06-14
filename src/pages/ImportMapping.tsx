import React, { useState } from 'react';
import { useLocation, useNavigate } from 'react-router-dom';
import { 
  ArrowRight, 
  CheckCircle, 
  AlertTriangle,
  Save,
  Play,
  FileText,
  Zap
} from 'lucide-react';

const ImportMapping = () => {
  const location = useLocation();
  const navigate = useNavigate();
  const { lotData, newLotId } = location.state || {};

  const [mapping, setMapping] = useState({
    productName: '',
    serialNumber: '',
    quantity: '',
    unitPrice: '',
    supplierProductId: ''
  });

  const [saveMappingProfile, setSaveMappingProfile] = useState(false);
  const [mappingProfileName, setMappingProfileName] = useState('');

  // Simulated file columns detected
  const detectedColumns = [
    'Désignation',
    'Modèle',
    'IMEI',
    'Numéro de série',
    'Qté',
    'Quantité',
    'Prix unitaire HT',
    'Coût',
    'Référence fournisseur',
    'ID produit',
    'État',
    'Grade'
  ];

  const requiredFields = [
    {
      key: 'productName',
      label: 'Nom du produit',
      description: 'Nom ou désignation du produit',
      required: true
    },
    {
      key: 'serialNumber',
      label: 'Numéro de série / IMEI',
      description: 'Identifiant unique du produit',
      required: false
    },
    {
      key: 'quantity',
      label: 'Quantité',
      description: 'Nombre d\'unités par ligne',
      required: true
    },
    {
      key: 'unitPrice',
      label: 'Prix unitaire HT',
      description: 'Coût d\'achat par unité',
      required: true
    },
    {
      key: 'supplierProductId',
      label: 'ID produit fournisseur',
      description: 'Référence interne du fournisseur',
      required: false
    }
  ];

  const existingProfiles = [
    'SYA Electronics - Format Standard',
    'TechSource Pro - Export Excel',
    'Mobile Discount - CSV Export'
  ];

  const handleMappingChange = (field: string, value: string) => {
    setMapping({ ...mapping, [field]: value });
  };

  const isValidMapping = () => {
    return mapping.productName && mapping.quantity && mapping.unitPrice;
  };

  const handleImport = () => {
    // Simulate import process and redirect to qualification
    navigate(`/lots/${newLotId}/qualification`, {
      state: { 
        lotData: {
          ...lotData,
          id: newLotId,
          mapping
        }
      }
    });
  };

  const loadMappingProfile = (profileName: string) => {
    // Simulate loading a saved mapping profile
    if (profileName === 'SYA Electronics - Format Standard') {
      setMapping({
        productName: 'Désignation',
        serialNumber: 'IMEI',
        quantity: 'Qté',
        unitPrice: 'Prix unitaire HT',
        supplierProductId: 'Référence fournisseur'
      });
    }
  };

  if (!lotData) {
    return (
      <div className="text-center py-12">
        <AlertTriangle className="w-12 h-12 text-amber-500 mx-auto mb-4" />
        <h2 className="text-xl font-semibold text-gray-900">Données manquantes</h2>
        <p className="text-gray-600 mt-2">Veuillez retourner à la création de lot.</p>
      </div>
    );
  }

  return (
    <div className="max-w-4xl mx-auto space-y-6">
      {/* Header */}
      <div>
        <h1 className="text-2xl font-bold text-gray-900">Configuration du Mapping</h1>
        <p className="text-gray-600 mt-2">
          Associez les colonnes de votre fichier aux champs du système
        </p>
        <div className="mt-4 bg-blue-50 border border-blue-200 rounded-lg p-4">
          <div className="flex items-center">
            <FileText className="w-5 h-5 text-blue-600 mr-2" />
            <div>
              <p className="font-medium text-blue-900">Lot: {lotData.name}</p>
              <p className="text-blue-700 text-sm">Fichier: {lotData.file?.name}</p>
            </div>
          </div>
        </div>
      </div>

      {/* Existing Profiles */}
      <div className="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h2 className="text-lg font-semibold text-gray-900 mb-4">Profils de Mapping Existants</h2>
        <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
          {existingProfiles.map((profile) => (
            <button
              key={profile}
              onClick={() => loadMappingProfile(profile)}
              className="p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors text-left"
            >
              <div className="flex items-center">
                <Zap className="w-5 h-5 text-yellow-500 mr-2" />
                <span className="font-medium text-gray-900">{profile}</span>
              </div>
              <p className="text-sm text-gray-500 mt-1">Cliquer pour charger</p>
            </button>
          ))}
        </div>
      </div>

      {/* Mapping Configuration */}
      <div className="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h2 className="text-lg font-semibold text-gray-900 mb-6">Configuration des Correspondances</h2>
        
        <div className="space-y-6">
          {requiredFields.map((field) => (
            <div key={field.key} className="grid grid-cols-1 md:grid-cols-2 gap-6 items-center p-4 border border-gray-200 rounded-lg">
              <div>
                <div className="flex items-center">
                  <h3 className="font-medium text-gray-900">{field.label}</h3>
                  {field.required && (
                    <span className="ml-2 text-red-500 text-sm">*</span>
                  )}
                </div>
                <p className="text-sm text-gray-500 mt-1">{field.description}</p>
              </div>
              
              <div className="flex items-center space-x-4">
                <select
                  value={mapping[field.key as keyof typeof mapping]}
                  onChange={(e) => handleMappingChange(field.key, e.target.value)}
                  className="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                >
                  <option value="">Sélectionner une colonne</option>
                  {detectedColumns.map((column) => (
                    <option key={column} value={column}>
                      {column}
                    </option>
                  ))}
                </select>
                
                {mapping[field.key as keyof typeof mapping] && (
                  <CheckCircle className="w-5 h-5 text-green-600" />
                )}
              </div>
            </div>
          ))}
        </div>
      </div>

      {/* Preview */}
      <div className="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h2 className="text-lg font-semibold text-gray-900 mb-4">Aperçu des Données</h2>
        <div className="overflow-x-auto">
          <table className="min-w-full divide-y divide-gray-200">
            <thead className="bg-gray-50">
              <tr>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                  Nom Produit
                </th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                  N° Série
                </th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                  Quantité
                </th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                  Prix
                </th>
              </tr>
            </thead>
            <tbody className="bg-white divide-y divide-gray-200">
              <tr>
                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  iPhone 11 64GB Black Grade B
                </td>
                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  351234567890123
                </td>
                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  1
                </td>
                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  €450.00
                </td>
              </tr>
              <tr>
                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  Samsung Galaxy S20 128GB Blue
                </td>
                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  351987654321098
                </td>
                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  2
                </td>
                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  €380.00
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      {/* Save Profile */}
      <div className="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div className="flex items-center space-x-4">
          <input
            type="checkbox"
            id="save-profile"
            checked={saveMappingProfile}
            onChange={(e) => setSaveMappingProfile(e.target.checked)}
            className="w-4 h-4 text-blue-600 rounded focus:ring-blue-500"
          />
          <label htmlFor="save-profile" className="text-sm font-medium text-gray-700">
            Sauvegarder ce mapping comme profil
          </label>
        </div>
        
        {saveMappingProfile && (
          <div className="mt-4">
            <input
              type="text"
              value={mappingProfileName}
              onChange={(e) => setMappingProfileName(e.target.value)}
              placeholder="Nom du profil (ex: SYA Electronics - Format 2025)"
              className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            />
          </div>
        )}
      </div>

      {/* Actions */}
      <div className="flex justify-between items-center">
        <button
          onClick={() => navigate('/create-lot')}
          className="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors"
        >
          Retour
        </button>

        <div className="flex space-x-4">
          {saveMappingProfile && (
            <button className="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors flex items-center">
              <Save className="w-4 h-4 mr-2" />
              Sauvegarder Profil
            </button>
          )}
          
          <button
            onClick={handleImport}
            disabled={!isValidMapping()}
            className="px-6 py-2 bg-blue-600 text-white rounded-lg disabled:opacity-50 disabled:cursor-not-allowed hover:bg-blue-700 transition-colors flex items-center"
          >
            <Play className="w-4 h-4 mr-2" />
            Importer et Continuer
          </button>
        </div>
      </div>
    </div>
  );
};

export default ImportMapping;