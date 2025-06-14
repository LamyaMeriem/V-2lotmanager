import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { 
  Upload, 
  FileText, 
  Users, 
  Calendar,
  AlertCircle,
  CheckCircle,
  ArrowRight
} from 'lucide-react';

const CreateLot = () => {
  const navigate = useNavigate();
  const [step, setStep] = useState(1);
  const [lotData, setLotData] = useState({
    name: '',
    supplier: '',
    file: null as File | null,
    fileType: ''
  });

  const suppliers = [
    'SYA Electronics',
    'TechSource Pro',
    'Mobile Discount',
    'Digital Supply',
    'RefurbTech',
    'GadgetWorld',
    'SmartDevice Co.'
  ];

  const handleFileUpload = (event: React.ChangeEvent<HTMLInputElement>) => {
    const file = event.target.files?.[0];
    if (file) {
      const fileExtension = file.name.split('.').pop()?.toLowerCase();
      setLotData({
        ...lotData,
        file,
        fileType: fileExtension || ''
      });
    }
  };

  const handleSubmit = () => {
    // Simulate lot creation and redirect to mapping
    navigate('/import-mapping', { 
      state: { 
        lotData,
        newLotId: `LOT-${new Date().toISOString().split('T')[0]}-${String(Math.floor(Math.random() * 999) + 1).padStart(3, '0')}`
      }
    });
  };

  const isStepValid = (stepNumber: number) => {
    switch (stepNumber) {
      case 1:
        return lotData.name.trim() !== '' && lotData.supplier !== '';
      case 2:
        return lotData.file !== null;
      default:
        return false;
    }
  };

  return (
    <div className="max-w-2xl mx-auto space-y-6">
      {/* Header */}
      <div className="text-center">
        <h1 className="text-2xl font-bold text-gray-900">Créer un Nouveau Lot</h1>
        <p className="text-gray-600 mt-2">Importez vos produits depuis un fichier et créez un nouveau lot</p>
      </div>

      {/* Progress Steps */}
      <div className="flex items-center justify-center space-x-8">
        <div className="flex items-center">
          <div className={`w-10 h-10 rounded-full flex items-center justify-center font-medium ${
            step >= 1 ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-500'
          }`}>
            1
          </div>
          <span className="ml-2 text-sm font-medium text-gray-700">Informations</span>
        </div>
        <ArrowRight className="w-5 h-5 text-gray-400" />
        <div className="flex items-center">
          <div className={`w-10 h-10 rounded-full flex items-center justify-center font-medium ${
            step >= 2 ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-500'
          }`}>
            2
          </div>
          <span className="ml-2 text-sm font-medium text-gray-700">Fichier</span>
        </div>
        <ArrowRight className="w-5 h-5 text-gray-400" />
        <div className="flex items-center">
          <div className={`w-10 h-10 rounded-full flex items-center justify-center font-medium ${
            step >= 3 ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-500'
          }`}>
            3
          </div>
          <span className="ml-2 text-sm font-medium text-gray-700">Validation</span>
        </div>
      </div>

      {/* Step Content */}
      <div className="bg-white rounded-xl shadow-sm border border-gray-200 p-8">
        {step === 1 && (
          <div className="space-y-6">
            <div className="text-center">
              <FileText className="w-12 h-12 text-blue-600 mx-auto mb-4" />
              <h2 className="text-xl font-semibold text-gray-900">Informations du Lot</h2>
              <p className="text-gray-500 mt-2">Définissez les informations de base de votre lot</p>
            </div>

            <div className="space-y-4">
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">
                  Nom du Lot *
                </label>
                <input
                  type="text"
                  value={lotData.name}
                  onChange={(e) => setLotData({ ...lotData, name: e.target.value })}
                  placeholder="Ex: Achat Fournisseur SYA - 15/01/2025"
                  className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                />
              </div>

              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">
                  Fournisseur *
                </label>
                <select
                  value={lotData.supplier}
                  onChange={(e) => setLotData({ ...lotData, supplier: e.target.value })}
                  className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                >
                  <option value="">Sélectionner un fournisseur</option>
                  {suppliers.map((supplier) => (
                    <option key={supplier} value={supplier}>
                      {supplier}
                    </option>
                  ))}
                </select>
              </div>

              <div className="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <div className="flex items-start">
                  <AlertCircle className="w-5 h-5 text-blue-600 mr-2 mt-0.5" />
                  <div className="text-sm">
                    <p className="font-medium text-blue-900">Informations importantes</p>
                    <p className="text-blue-700 mt-1">
                      Le nom du lot vous aidera à identifier facilement vos achats. 
                      Nous vous recommandons d'inclure le fournisseur et la date.
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        )}

        {step === 2 && (
          <div className="space-y-6">
            <div className="text-center">
              <Upload className="w-12 h-12 text-blue-600 mx-auto mb-4" />
              <h2 className="text-xl font-semibold text-gray-900">Import du Fichier</h2>
              <p className="text-gray-500 mt-2">Téléchargez votre fichier contenant la liste des produits</p>
            </div>

            <div className="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center hover:border-blue-400 transition-colors">
              <input
                type="file"
                id="file-upload"
                accept=".xlsx,.xls,.csv,.pdf"
                onChange={handleFileUpload}
                className="hidden"
              />
              <label htmlFor="file-upload" className="cursor-pointer">
                <Upload className="w-8 h-8 text-gray-400 mx-auto mb-4" />
                <p className="text-lg font-medium text-gray-900 mb-2">
                  Cliquez pour sélectionner un fichier
                </p>
                <p className="text-gray-500">
                  Formats supportés: .xlsx, .csv, .pdf (max 10MB)
                </p>
              </label>
            </div>

            {lotData.file && (
              <div className="bg-green-50 border border-green-200 rounded-lg p-4">
                <div className="flex items-center">
                  <CheckCircle className="w-5 h-5 text-green-600 mr-3" />
                  <div>
                    <p className="font-medium text-green-900">Fichier sélectionné</p>
                    <p className="text-green-700 text-sm">
                      {lotData.file.name} ({(lotData.file.size / 1024 / 1024).toFixed(2)} MB)
                    </p>
                  </div>
                </div>
              </div>
            )}

            <div className="bg-amber-50 border border-amber-200 rounded-lg p-4">
              <div className="flex items-start">
                <AlertCircle className="w-5 h-5 text-amber-600 mr-2 mt-0.5" />
                <div className="text-sm">
                  <p className="font-medium text-amber-900">Conseils pour un import réussi</p>
                  <ul className="text-amber-700 mt-1 space-y-1">
                    <li>• Assurez-vous que votre fichier contient les colonnes: nom produit, quantité, prix</li>
                    <li>• Les numéros de série/IMEI sont recommandés pour la traçabilité</li>
                    <li>• Vérifiez que les données sont bien formatées</li>
                  </ul>
                </div>
              </div>
            </div>
          </div>
        )}

        {step === 3 && (
          <div className="space-y-6">
            <div className="text-center">
              <CheckCircle className="w-12 h-12 text-green-600 mx-auto mb-4" />
              <h2 className="text-xl font-semibold text-gray-900">Validation du Lot</h2>
              <p className="text-gray-500 mt-2">Vérifiez les informations avant de procéder à l'import</p>
            </div>

            <div className="bg-gray-50 rounded-lg p-6 space-y-4">
              <div className="grid grid-cols-2 gap-4">
                <div>
                  <label className="text-sm font-medium text-gray-500">Nom du Lot</label>
                  <p className="font-medium text-gray-900">{lotData.name}</p>
                </div>
                <div>
                  <label className="text-sm font-medium text-gray-500">Fournisseur</label>
                  <p className="font-medium text-gray-900">{lotData.supplier}</p>
                </div>
                <div>
                  <label className="text-sm font-medium text-gray-500">Fichier</label>
                  <p className="font-medium text-gray-900">{lotData.file?.name}</p>
                </div>
                <div>
                  <label className="text-sm font-medium text-gray-500">Type</label>
                  <p className="font-medium text-gray-900 uppercase">{lotData.fileType}</p>
                </div>
              </div>
            </div>

            <div className="bg-blue-50 border border-blue-200 rounded-lg p-4">
              <div className="flex items-start">
                <AlertCircle className="w-5 h-5 text-blue-600 mr-2 mt-0.5" />
                <div className="text-sm">
                  <p className="font-medium text-blue-900">Prochaine étape</p>
                  <p className="text-blue-700 mt-1">
                    Après validation, vous serez dirigé vers l'interface de mapping pour 
                    faire correspondre les colonnes de votre fichier avec les champs du système.
                  </p>
                </div>
              </div>
            </div>
          </div>
        )}
      </div>

      {/* Navigation Buttons */}
      <div className="flex justify-between">
        <button
          onClick={() => step > 1 && setStep(step - 1)}
          disabled={step === 1}
          className="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50 transition-colors"
        >
          Précédent
        </button>

        {step < 3 ? (
          <button
            onClick={() => isStepValid(step) && setStep(step + 1)}
            disabled={!isStepValid(step)}
            className="px-6 py-2 bg-blue-600 text-white rounded-lg disabled:opacity-50 disabled:cursor-not-allowed hover:bg-blue-700 transition-colors"
          >
            Suivant
          </button>
        ) : (
          <button
            onClick={handleSubmit}
            className="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors"
          >
            Créer le Lot
          </button>
        )}
      </div>
    </div>
  );
};

export default CreateLot;