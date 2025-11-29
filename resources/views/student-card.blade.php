<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kartu Siswa - {{ $student->user->name }}</title>
    <style>
        @media print {
            @page {
                size: 85.6mm 53.98mm landscape; /* Standard ID card size */
                margin: 0;
            }
            body {
                margin: 0;
                padding: 0;
                background: white;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                color-adjust: exact !important;
            }
            .no-print {
                display: none !important;
            }
            .card-container {
                display: block !important;
                page-break-after: always;
                box-shadow: none;
                margin: 0;
            }
            .card-container:last-child {
                page-break-after: auto;
            }
            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                color-adjust: exact !important;
            }
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
            color-adjust: exact !important;
        }

        body {
            font-family: 'Arial', sans-serif;
            background: #f3f4f6;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        /* Print Controls */
        .print-controls {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .print-button {
            background: #1E40AF;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            box-shadow: 0 4px 12px rgba(30, 64, 175, 0.3);
            transition: all 0.3s;
        }

        .print-button:hover {
            background: #1E3A8A;
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(30, 64, 175, 0.4);
        }

        .info-banner {
            background: #EFF6FF;
            border-left: 4px solid #3B82F6;
            padding: 16px;
            margin-bottom: 20px;
            border-radius: 4px;
        }

        /* Cards Wrapper */
        .cards-wrapper {
            display: flex;
            gap: 20px;
            justify-content: center;
            flex-wrap: wrap;
            padding: 20px;
            background: white;
            border-radius: 8px;
        }

        .card-container {
            width: 85.6mm; /* 3.375 inches */
            height: 53.98mm; /* 2.125 inches */
            background: white;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            border-radius: 8px;
            overflow: hidden;
            position: relative;
        }

        /* ========== FRONT SIDE ========== */
        .card-front {
            border: 2px solid #1E40AF;
        }

        .card-front .card-header {
            background: linear-gradient(135deg, #1E40AF 0%, #3B82F6 100%);
            color: white;
            padding: 6px 10px;
            text-align: center;
        }

        .card-front .school-logo {
            width: 28px;
            height: 28px;
            background: #FBBF24;
            border-radius: 50%;
            margin: 0 auto 3px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 14px;
        }

        .card-front .school-name {
            font-size: 8px;
            font-weight: bold;
            line-height: 1.2;
        }

        .card-front .school-subtitle {
            font-size: 6px;
            opacity: 0.9;
            margin-top: 2px;
        }

        .card-front .card-body {
            padding: 8px 10px;
            display: flex;
            gap: 8px;
            height: calc(100% - 55px);
        }

        .card-front .photo-section {
            flex-shrink: 0;
        }

        .card-front .photo {
            width: 65px;
            height: 85px;
            background: #E5E7EB;
            border: 2px solid #1E40AF;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 9px;
            color: #6B7280;
            text-align: center;
            padding: 4px;
            overflow: hidden;
        }

        .card-front .photo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .card-front .info-section {
            flex: 1;
            font-size: 7.5px;
            display: flex;
            flex-direction: column;
        }

        .card-front .student-name {
            font-size: 10px;
            font-weight: bold;
            color: #1E40AF;
            margin-bottom: 5px;
            text-transform: uppercase;
            line-height: 1.2;
        }

        .card-front .info-row {
            margin-bottom: 3px;
            display: flex;
            align-items: flex-start;
        }

        .card-front .info-label {
            font-weight: 600;
            color: #4B5563;
            min-width: 35px;
        }

        .card-front .info-value {
            color: #111827;
            font-weight: 500;
            flex: 1;
        }

        .card-front .barcode-section {
            margin-top: auto;
            background: white;
            padding: 4px;
            border: 1px solid #E5E7EB;
            border-radius: 3px;
            text-align: center;
        }

        .card-front .barcode-image {
            width: 100%;
            height: 25px;
            background: repeating-linear-gradient(
                90deg,
                #000 0px,
                #000 2px,
                #fff 2px,
                #fff 4px
            );
            margin-bottom: 2px;
        }

        .card-front .barcode-text {
            font-family: 'Courier New', monospace;
            font-size: 7px;
            font-weight: bold;
            color: #111827;
        }

        .card-front .card-footer {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: #1E40AF;
            padding: 3px 10px;
            font-size: 6px;
            color: white;
            text-align: center;
        }

        /* ========== BACK SIDE ========== */
        .card-back {
            border: 2px solid #8B5CF6;
            background: linear-gradient(135deg, #F9FAFB 0%, #F3F4F6 100%);
        }

        .card-back .rfid-header {
            background: linear-gradient(135deg, #8B5CF6 0%, #A78BFA 100%);
            color: white;
            padding: 8px 10px;
            text-align: center;
        }

        .card-back .rfid-icon {
            width: 32px;
            height: 32px;
            background: rgba(255,255,255,0.2);
            border-radius: 50%;
            margin: 0 auto 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
        }

        .card-back .rfid-title {
            font-size: 9px;
            font-weight: bold;
            margin-bottom: 3px;
        }

        .card-back .rfid-uid-display {
            font-family: 'Courier New', monospace;
            font-size: 11px;
            font-weight: bold;
            background: rgba(255,255,255,0.25);
            padding: 4px 8px;
            border-radius: 4px;
            letter-spacing: 1px;
        }

        .card-back .card-body {
            padding: 8px 10px;
        }

        .card-back .info-block {
            margin-bottom: 6px;
        }

        .card-back .info-block-title {
            font-size: 7px;
            font-weight: bold;
            color: #8B5CF6;
            margin-bottom: 3px;
            text-transform: uppercase;
        }

        .card-back .info-block-content {
            font-size: 6.5px;
            color: #374151;
            line-height: 1.4;
        }

        .card-back .validity-section {
            background: white;
            padding: 5px;
            border-radius: 4px;
            margin-bottom: 5px;
            border: 1px solid #E5E7EB;
        }

        .card-back .validity-row {
            display: flex;
            justify-content: space-between;
            font-size: 6.5px;
            margin-bottom: 2px;
        }

        .card-back .validity-label {
            font-weight: 600;
            color: #6B7280;
        }

        .card-back .validity-value {
            font-weight: bold;
            color: #111827;
        }

        .card-back .emergency-box {
            background: #FEF3C7;
            border: 1px solid #F59E0B;
            padding: 4px 6px;
            border-radius: 4px;
            margin-bottom: 5px;
        }

        .card-back .emergency-title {
            font-size: 6.5px;
            font-weight: bold;
            color: #92400E;
            margin-bottom: 2px;
        }

        .card-back .emergency-text {
            font-size: 6px;
            color: #78350F;
            line-height: 1.3;
        }

        .card-back .signature-section {
            display: flex;
            justify-content: space-between;
            gap: 5px;
        }

        .card-back .signature-box {
            flex: 1;
            text-align: center;
        }

        .card-back .signature-line {
            width: 100%;
            height: 15px;
            border-bottom: 1px solid #9CA3AF;
            margin-bottom: 2px;
        }

        .card-back .signature-label {
            font-size: 6px;
            color: #6B7280;
        }

        .card-back .card-footer {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: #8B5CF6;
            padding: 3px 10px;
            font-size: 6px;
            color: white;
            text-align: center;
        }

        .status-badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 6.5px;
            font-weight: bold;
        }

        .status-active {
            background: #D1FAE5;
            color: #065F46;
        }

        .status-unregistered {
            background: #FEE2E2;
            color: #991B1B;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Print Controls -->
        <div class="print-controls no-print">
            <div>
                <h2 style="font-size: 20px; color: #1E40AF; margin-bottom: 8px;">
                    Kartu Siswa 2-Sided - {{ $student->user->name }}
                </h2>
                <p style="font-size: 14px; color: #6B7280;">
                    NISN: <strong>{{ $student->nis }}</strong> | Kelas: <strong>{{ $student->kelas }}</strong>
                </p>
            </div>
            <button onclick="window.print()" class="print-button">
                🖨️ Print Kartu
            </button>
        </div>

        <!-- Info Banner -->
        <div class="info-banner no-print">
            <p style="font-size: 14px; color: #1E40AF; margin-bottom: 8px;">
                <strong>💡 Panduan Pencetakan Kartu:</strong>
            </p>
            <ul style="font-size: 13px; color: #1E40AF; margin-left: 20px; line-height: 1.8;">
                <li>Gunakan kertas PVC card atau karton tebal (ukuran standar 85.6mm x 53.98mm)</li>
                <li>Halaman 1 = Bagian Depan (Biru), Halaman 2 = Bagian Belakang (Ungu)</li>
                <li>Print halaman depan terlebih dahulu, lalu balik kertas dan print halaman belakang</li>
                <li>Pastikan orientasi kertas sama saat print bolak-balik</li>
                <li>Aktifkan "Print backgrounds" di pengaturan print browser</li>
                <li>Gunakan setting print quality "High" atau "Best"</li>
                <li>Laminasi kartu setelah print untuk hasil terbaik</li>
            </ul>
        </div>

        <!-- Preview Cards (No Print) -->
        <div class="cards-wrapper no-print">
            <!-- FRONT SIDE PREVIEW -->
            <div class="card-container card-front">
                <!-- Header -->
                <div class="card-header">
                    <div class="school-logo">🎓</div>
                    <div class="school-name">KOPERASI LEMDIKLAT<br>TARUNA NUSANTARA INDONESIA</div>
                    <div class="school-subtitle">{{ $student->jenjang ?? 'Taruna Nusantara' }}</div>
                </div>

                <!-- Body -->
                <div class="card-body">
                    <!-- Photo -->
                    <div class="photo-section">
                        <div class="photo">
                            @if($student->user->photo)
                                <img src="{{ asset('storage/' . $student->user->photo) }}" alt="Foto Siswa">
                            @else
                                <div>FOTO<br>SISWA<br>3x4</div>
                            @endif
                        </div>
                    </div>

                    <!-- Info -->
                    <div class="info-section">
                        <div class="student-name">{{ $student->user->name }}</div>

                        <div class="info-row">
                            <div class="info-label">NISN</div>
                            <div class="info-value">: {{ $student->nis }}</div>
                        </div>

                        @if($student->nisn)
                        <div class="info-row">
                            <div class="info-label">NISN Nasional</div>
                            <div class="info-value">: {{ $student->nisn }}</div>
                        </div>
                        @endif

                        <div class="info-row">
                            <div class="info-label">Kelas</div>
                            <div class="info-value">: {{ $student->kelas }}</div>
                        </div>

                        <div class="info-row">
                            <div class="info-label">Jenjang</div>
                            <div class="info-value">: {{ $student->jenjang ?? '-' }}</div>
                        </div>

                        <div class="info-row" style="margin-top: 4px;">
                            <div class="info-label" style="font-weight: bold; color: #4338CA;">RFID UID</div>
                            <div class="info-value" style="font-family: 'Courier New', monospace; font-weight: bold; color: #4338CA; letter-spacing: 1px;">
                                @if($student->rfid_uid)
                                    : {{ $student->rfid_uid }}
                                @else
                                    : <span style="color: #DC2626;">BELUM TERDAFTAR</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="card-footer">
                    KARTU IDENTITAS SISWA | TAHUN AJARAN {{ date('Y') }}/{{ date('Y') + 1 }}
                </div>
            </div>

            <!-- BACK SIDE PREVIEW -->
            <div class="card-container card-back">
                <!-- RFID Header -->
                <div class="rfid-header">
                    <div class="rfid-icon">📡</div>
                    <div class="rfid-title">STUDENT ID CARD</div>
                    <div class="rfid-uid-display" style="background: rgba(255,255,255,0.25); font-size: 9px;">
                        KOPERASI LEMDIKLAT Taruna Nusantara Indonesia
                    </div>
                </div>

                <!-- Body -->
                <div class="card-body">
                    <!-- School Info -->
                    <div class="info-block">
                        <div class="info-block-title">📍 Informasi Sekolah</div>
                        <div class="info-block-content">
                            {{ $school_config['name'] ?? 'Koperasi Lemdiklat Taruna Nusantara Indonesia' }}<br>
                            {{ $student->jenjang ?? 'SMA Taruna Nusantara Indonesia/SMK Taruna Nusantara Jaya' }}<br>
                            {{ $school_config['address'] ?? 'Kab. Bandung Barat' }}<br>
                            Telp: {{ $school_config['phone'] ?? '(021) XXXX-XXXX' }}
                        </div>
                    </div>

                    <!-- Validity Period -->
                    <div class="validity-section">
                        <div class="validity-row">
                            <span class="validity-label">Berlaku dari:</span>
                            <span class="validity-value">{{ date('d/m/Y') }}</span>
                        </div>
                        <div class="validity-row">
                            <span class="validity-label">Berlaku hingga:</span>
                            <span class="validity-value">{{ date('d/m/Y', strtotime('+3 year')) }}</span>
                        </div>
                        <div class="validity-row">
                            <span class="validity-label">Status:</span>
                            @if($student->rfid_uid)
                                <span class="status-badge status-active">● ACTIVE</span>
                            @else
                                <span class="status-badge status-unregistered">● UNREGISTERED</span>
                            @endif
                        </div>
                    </div>

                    <!-- Emergency Instructions -->
                    <div class="emergency-box">
                        <div class="emergency-title">⚠️ JIKA KARTU HILANG</div>
                        <div class="emergency-text">
                            Segera laporkan ke bagian administrasi sekolah.<br>
                            Kartu yang hilang akan dinonaktifkan demi keamanan saldo Anda.
                        </div>
                    </div>

                    <!-- Signature Section -->
                    <div class="signature-section">
                        <div class="signature-box">
                            <div class="signature-line"></div>
                            <div class="signature-label">Pemegang Kartu</div>
                        </div>
                        <div class="signature-box">
                            <div class="signature-line"></div>
                            <div class="signature-label">Kepala Sekolah</div>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="card-footer">
                    https://lemdiklattarunanusantaraindonesia.id | Kartu ini tidak dapat dipindahtangankan
                </div>
            </div>
        </div>

        <!-- HALAMAN 1: BAGIAN DEPAN (For Print Only) -->
        <div class="card-container card-front" style="display: none;">
            <!-- Header -->
            <div class="card-header">
                <div class="school-logo">🎓</div>
                <div class="school-name">KOPERASI LEMDIKLAT<br>TARUNA NUSANTARA INDONESIA</div>
                <div class="school-subtitle">{{ $student->jenjang ?? 'Taruna Nusantara' }}</div>
            </div>

            <!-- Body -->
            <div class="card-body">
                <!-- Photo -->
                <div class="photo-section">
                    <div class="photo">
                        @if($student->user->photo)
                            <img src="{{ asset('storage/' . $student->user->photo) }}" alt="Foto Siswa">
                        @else
                            <div>FOTO<br>SISWA<br>3x4</div>
                        @endif
                    </div>
                </div>

                <!-- Info -->
                <div class="info-section">
                    <div class="student-name">{{ $student->user->name }}</div>

                    <div class="info-row">
                        <div class="info-label">NISN</div>
                        <div class="info-value">: {{ $student->nis }}</div>
                    </div>

                    @if($student->nisn)
                    <div class="info-row">
                        <div class="info-label">NISN Nasional</div>
                        <div class="info-value">: {{ $student->nisn }}</div>
                    </div>
                    @endif

                    <div class="info-row">
                        <div class="info-label">Kelas</div>
                        <div class="info-value">: {{ $student->kelas }}</div>
                    </div>

                    <div class="info-row">
                        <div class="info-label">Jenjang</div>
                        <div class="info-value">: {{ $student->jenjang ?? '-' }}</div>
                    </div>

                    <div class="info-row" style="margin-top: 4px;">
                        <div class="info-label" style="font-weight: bold; color: #4338CA;">RFID UID</div>
                        <div class="info-value" style="font-family: 'Courier New', monospace; font-weight: bold; color: #4338CA; letter-spacing: 1px;">
                            @if($student->rfid_uid)
                                : {{ $student->rfid_uid }}
                            @else
                                : <span style="color: #DC2626;">BELUM TERDAFTAR</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="card-footer">
                KARTU IDENTITAS SISWA | TAHUN AJARAN {{ date('Y') }}/{{ date('Y') + 1 }}
            </div>
        </div>

        <!-- HALAMAN 2: BAGIAN BELAKANG (For Print Only) -->
        <div class="card-container card-back" style="display: none;">
            <!-- RFID Header -->
            <div class="rfid-header">
                <div class="rfid-icon">📡</div>
                <div class="rfid-title">STUDENT ID CARD</div>
                <div class="rfid-uid-display" style="background: rgba(255,255,255,0.25); font-size: 9px;">
                    KOPERASI LEMDIKLAT Taruna Nusantara Indonesia
                </div>
            </div>

            <!-- Body -->
            <div class="card-body">
                <!-- School Info -->
                <div class="info-block">
                    <div class="info-block-title">📍 Informasi Sekolah</div>
                    <div class="info-block-content">
                        {{ $school_config['name'] ?? 'Koperasi Lemdiklat Taruna Nusantara Indonesia' }}<br>
                        {{ $student->jenjang ?? 'SMA Taruna Nusantara Indonesia/SMK Taruna Nusantara Jaya' }}<br>
                        {{ $school_config['address'] ?? 'Kab. Bandung Barat' }}<br>
                        Telp: {{ $school_config['phone'] ?? '(021) XXXX-XXXX' }}
                    </div>
                </div>

                <!-- Validity Period -->
                <div class="validity-section">
                    <div class="validity-row">
                        <span class="validity-label">Berlaku dari:</span>
                        <span class="validity-value">{{ date('d/m/Y') }}</span>
                    </div>
                    <div class="validity-row">
                        <span class="validity-label">Berlaku hingga:</span>
                        <span class="validity-value">{{ date('d/m/Y', strtotime('+3 year')) }}</span>
                    </div>
                    <div class="validity-row">
                        <span class="validity-label">Status:</span>
                        @if($student->rfid_uid)
                            <span class="status-badge status-active">● ACTIVE</span>
                        @else
                            <span class="status-badge status-unregistered">● UNREGISTERED</span>
                        @endif
                    </div>
                </div>

                <!-- Emergency Instructions -->
                <div class="emergency-box">
                    <div class="emergency-title">⚠️ JIKA KARTU HILANG</div>
                    <div class="emergency-text">
                        Segera laporkan ke bagian administrasi sekolah.<br>
                        Kartu yang hilang akan dinonaktifkan demi keamanan saldo Anda.
                    </div>
                </div>

                <!-- Signature Section -->
                <div class="signature-section">
                    <div class="signature-box">
                        <div class="signature-line"></div>
                        <div class="signature-label">Pemegang Kartu</div>
                    </div>
                    <div class="signature-box">
                        <div class="signature-line"></div>
                        <div class="signature-label">Kepala Sekolah</div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="card-footer">
                https://lemdiklattarunanusantaraindonesia.id | Kartu ini tidak dapat dipindahtangankan
            </div>
        </div>
    </div>

    <script>
        // Auto print on page load (optional)
        // window.onload = function() { setTimeout(function(){ window.print(); }, 500); }
    </script>
</body>
</html>
