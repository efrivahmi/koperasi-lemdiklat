<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kartu Siswa - {{ $student->user->name }}</title>
    <style>
        @media print {
            @page {
                size: 85.6mm 53.98mm; /* Standard ID card size */
                margin: 0;
            }
            body {
                margin: 0;
                padding: 0;
            }
            .no-print {
                display: none !important;
            }
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            background: #f3f4f6;
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .card-container {
            width: 85.6mm; /* 3.375 inches */
            height: 53.98mm; /* 2.125 inches */
            background: white;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            border-radius: 8px;
            overflow: hidden;
            position: relative;
        }

        .card-header {
            background: linear-gradient(135deg, #1E40AF 0%, #3B82F6 100%);
            color: white;
            padding: 8px 12px;
            text-align: center;
        }

        .school-logo {
            width: 30px;
            height: 30px;
            background: #FBBF24;
            border-radius: 50%;
            margin: 0 auto 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 16px;
        }

        .school-name {
            font-size: 9px;
            font-weight: bold;
            line-height: 1.2;
        }

        .school-subtitle {
            font-size: 7px;
            opacity: 0.9;
            margin-top: 2px;
        }

        .card-body {
            padding: 10px 12px;
            display: flex;
            gap: 10px;
        }

        .photo-section {
            flex-shrink: 0;
        }

        .photo {
            width: 80px;
            height: 100px;
            background: #E5E7EB;
            border: 2px solid #1E40AF;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            color: #6B7280;
            text-align: center;
            padding: 5px;
            position: relative;
            overflow: hidden;
        }

        .photo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .info-section {
            flex: 1;
            font-size: 8px;
        }

        .student-name {
            font-size: 11px;
            font-weight: bold;
            color: #1E40AF;
            margin-bottom: 6px;
            text-transform: uppercase;
        }

        .info-row {
            margin-bottom: 4px;
            display: flex;
        }

        .info-label {
            font-weight: 600;
            color: #4B5563;
            min-width: 40px;
        }

        .info-value {
            color: #111827;
            font-weight: 500;
        }

        .rfid-section {
            background: #F3F4F6;
            padding: 6px;
            margin-top: 6px;
            border-radius: 4px;
            text-align: center;
        }

        .rfid-label {
            font-size: 7px;
            color: #6B7280;
            margin-bottom: 2px;
        }

        .rfid-uid {
            font-family: 'Courier New', monospace;
            font-size: 9px;
            font-weight: bold;
            color: #1E40AF;
            letter-spacing: 0.5px;
        }

        .card-footer {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: #F9FAFB;
            padding: 4px 12px;
            font-size: 6px;
            color: #6B7280;
            text-align: center;
            border-top: 1px solid #E5E7EB;
        }

        /* Print Button */
        .print-button {
            position: fixed;
            top: 20px;
            right: 20px;
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
    </style>
</head>
<body>
    <button onclick="window.print()" class="print-button no-print">🖨️ Print Kartu</button>

    <div class="card-container">
        <!-- Header -->
        <div class="card-header">
            <div class="school-logo">🎓</div>
            <div class="school-name">KOPERASI LEMDIKLAT<br>TARUNA NUSANTARA INDONESIA</div>
            <div class="school-subtitle">{{ $student->jenjang ?? 'SMA/SMK Taruna Nusantara' }}</div>
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
                    <div class="info-label">NIS</div>
                    <div class="info-value">: {{ $student->nis }}</div>
                </div>

                @if($student->nisn)
                <div class="info-row">
                    <div class="info-label">NISN</div>
                    <div class="info-value">: {{ $student->nisn }}</div>
                </div>
                @endif

                <div class="info-row">
                    <div class="info-label">Kelas</div>
                    <div class="info-value">: {{ $student->kelas }}</div>
                </div>

                @if($student->alamat_lengkap)
                <div class="info-row">
                    <div class="info-label">Alamat</div>
                    <div class="info-value">: {{ Str::limit($student->alamat_lengkap, 50) }}</div>
                </div>
                @endif

                <!-- RFID Section -->
                @if($student->rfid_uid)
                <div class="rfid-section">
                    <div class="rfid-label">RFID UID</div>
                    <div class="rfid-uid">{{ $student->rfid_uid }}</div>
                </div>
                @else
                <div class="rfid-section" style="background: #FEF3C7;">
                    <div class="rfid-label" style="color: #92400E;">RFID BELUM TERDAFTAR</div>
                    <div style="font-size: 7px; color: #92400E; margin-top: 2px;">Hubungi admin untuk registrasi</div>
                </div>
                @endif
            </div>
        </div>

        <!-- Footer -->
        <div class="card-footer">
            Kartu ini adalah identitas resmi siswa Koperasi Lemdiklat TNI | Jika ditemukan, harap kembalikan
        </div>
    </div>

    <script>
        // Auto print on page load (opsional)
        // window.onload = function() { setTimeout(function(){ window.print(); }, 500); }
    </script>
</body>
</html>
