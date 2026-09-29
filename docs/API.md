# MotoCare REST API v1

## Base URL

## Format Response Standar

**Sukses:**
```json
{ "success": true, "message": "...", "data": {} }
```

**Error:**
```json
{ "success": false, "message": "...", "errors": {} }
```

## HTTP Status Code
| Code | Arti |
|---|---|
| 200 | Sukses (GET/update) |
| 201 | Resource berhasil dibuat |
| 401 | Belum login / token tidak valid |
| 403 | Tidak punya izin (Policy menolak) |
| 404 | Resource tidak ditemukan |
| 422 | Validasi gagal / business rule ditolak |
| 429 | Rate limit terlampaui |

---

## Health
| Method | URL | Auth | Deskripsi |
|---|---|---|---|
| GET | `/health` | Public | Cek API berjalan |

## Auth
| Method | URL | Auth | Role | Body |
|---|---|---|---|---|
| POST | `/auth/login` | Public (throttle 5/menit) | - | `email`, `password` |
| POST | `/auth/logout` | Sanctum | Semua | - |
| GET | `/auth/me` | Sanctum | Semua | - |

Login response: `data.user` (id, name, email, role), `data.token`.

## Vehicle
| Method | URL | Auth | Role |
|---|---|---|---|
| GET | `/vehicles` | Sanctum | Customer (miliknya), Admin (semua) |
| POST | `/vehicles` | Sanctum | Customer, Admin |
| GET | `/vehicles/{vehicle}` | Sanctum | Owner, Admin |
| PUT/PATCH | `/vehicles/{vehicle}` | Sanctum | Owner, Admin |
| DELETE | `/vehicles/{vehicle}` | Sanctum | Owner, Admin (ditolak 422 jika ada booking aktif) |

Body store/update: `nomor_polisi`, `merk`, `model`, `tahun`, `tipe_mesin`, `transmisi`, `warna?`, `nomor_rangka?`, `catatan?`. `user_id` selalu dari token, diabaikan jika dikirim.

## Booking
| Method | URL | Auth | Role |
|---|---|---|---|
| GET | `/bookings` | Sanctum | Customer (miliknya), Admin (semua) |
| POST | `/bookings` | Sanctum | Customer, Admin |
| GET | `/bookings/{booking}` | Sanctum | Owner, mekanik ditugaskan, kurir ditugaskan, Admin |
| POST | `/bookings/{booking}/cancel` | Sanctum | Owner (status pending/confirmed), Admin |
| POST | `/bookings/{booking}/confirm` | Sanctum | Admin saja |

Body store: `vehicle_id`, `tanggal`, `waktu`, `keluhan`, `jenis_layanan`, `pickup_requested?`, `alamat_pickup?`, `estimated_distance_km?`. `vehicle_id` diverifikasi kepemilikan.

## Service Order
| Method | URL | Auth | Role |
|---|---|---|---|
| GET | `/service-orders` | Sanctum | Mekanik (miliknya), Customer (via booking), Admin |
| GET | `/service-orders/{service_order}` | Sanctum | Sama seperti di atas |
| POST | `/service-orders/{service_order}/start` | Sanctum | Mekanik pemilik, Admin |
| POST | `/service-orders/{service_order}/diagnosis` | Sanctum | Mekanik pemilik, Admin (Customer 403) |
| POST | `/service-orders/{service_order}/complete` | Sanctum | Mekanik pemilik, Admin |

Body diagnosis: `diagnosis_mechanic`, `notes?`.

## Service Item (Master Jasa & Sparepart)
| Method | URL | Auth | Role |
|---|---|---|---|
| GET | `/service-items` | Sanctum | Semua (non-admin hanya lihat aktif) |
| GET | `/service-items/{service_item}` | Sanctum | Semua |
| POST | `/service-items` | Sanctum | Admin saja |
| PUT/PATCH | `/service-items/{service_item}` | Sanctum | Admin saja |
| PATCH | `/service-items/{service_item}/stock` | Sanctum | Admin saja |
| DELETE | `/service-items/{service_item}` | Sanctum | Admin saja (nonaktifkan) |

Harga (`price`) hanya bisa diubah admin lewat endpoint ini — tidak pernah dari transaksi.

## Payment
| Method | URL | Auth | Role |
|---|---|---|---|
| POST | `/service-orders/{service_order}/payments` | Sanctum | Customer pemilik order, Admin |
| GET | `/payments/{payment}` | Sanctum | Owner, Admin |
| POST | `/payments/{payment}/verify` | Sanctum | Admin saja |
| POST | `/payments/{payment}/reject` | Sanctum | Admin saja |

`amount` selalu dari `service_order.grand_total` server, tidak pernah dari request. Proof upload: mimes jpg/jpeg/png/pdf, maks 2MB.

## Delivery / Courier
| Method | URL | Auth | Role |
|---|---|---|---|
| GET | `/delivery-tasks` | Sanctum | Courier (miliknya), Customer (via booking), Admin |
| GET | `/delivery-tasks/{delivery_task}` | Sanctum | Sama seperti di atas |
| POST | `/delivery-tasks/{delivery_task}/assign` | Sanctum | Admin saja |
| POST | `/delivery-tasks/{delivery_task}/start` | Sanctum | Kurir ditugaskan, Admin |
| POST | `/delivery-tasks/{delivery_task}/complete` | Sanctum | Kurir ditugaskan, Admin |
| GET | `/delivery-settings` | Sanctum | Semua |
| PUT | `/delivery-settings` | Sanctum | Admin saja |

`delivery_fee` di-snapshot saat task dibuat — perubahan tarif admin tidak mengubah fee task lama.

## Invoice (Read-Only)
| Method | URL | Auth | Role |
|---|---|---|---|
| GET | `/invoices` | Sanctum | Ringkasan sesuai kepemilikan |
| GET | `/invoices/{service_order}` | Sanctum | Owner, mekanik pemilik, Admin |

Semua angka dari snapshot historis — tidak dihitung ulang dari harga master saat ini.

## Notification
| Method | URL | Auth | Role |
|---|---|---|---|
| GET | `/notifications` | Sanctum | Milik sendiri saja |
| GET | `/notifications/unread` | Sanctum | Milik sendiri saja |
| POST | `/notifications/{notification}/read` | Sanctum | Owner saja |

## Dashboard (Read-Only)
| Method | URL | Auth | Role |
|---|---|---|---|
| GET | `/dashboard` | Sanctum | Response menyesuaikan role login otomatis |
