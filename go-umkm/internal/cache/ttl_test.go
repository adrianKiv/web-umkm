package cache

import (
	"context"
	"errors"
	"sync"
	"sync/atomic"
	"testing"
	"time"
)

func TestMemuatSekaliSaja(t *testing.T) {
	c := New[int](time.Minute)
	calls := 0
	loader := func(context.Context) (int, error) {
		calls++
		return 42, nil
	}
	ctx := context.Background()

	for i := 0; i < 5; i++ {
		v, err := c.Get(ctx, loader)
		if err != nil || v != 42 {
			t.Fatalf("Get ke-%d = %v, %v", i, v, err)
		}
	}
	if calls != 1 {
		t.Errorf("loader dipanggil %d kali, mau 1", calls)
	}
}

func TestKedaluwarsa(t *testing.T) {
	c := New[string](time.Minute)
	waktu := time.Date(2026, 1, 1, 0, 0, 0, 0, time.UTC)
	c.now = func() time.Time { return waktu }

	calls := 0
	loader := func(context.Context) (string, error) {
		calls++
		return "v" + string(rune('0'+calls)), nil
	}
	ctx := context.Background()

	if _, err := c.Get(ctx, loader); err != nil {
		t.Fatal(err)
	}
	waktu = waktu.Add(61 * time.Second) // lewat TTL

	v, err := c.Get(ctx, loader)
	if err != nil {
		t.Fatal(err)
	}
	if calls != 2 || v != "v2" {
		t.Errorf("setelah kedaluwarsa: calls=%d val=%q, mau 2 dan v2", calls, v)
	}
}

func TestErrorTidakDisimpan(t *testing.T) {
	c := New[int](time.Minute)
	gagal := true
	loader := func(context.Context) (int, error) {
		if gagal {
			return 0, errors.New("db down")
		}
		return 7, nil
	}
	ctx := context.Background()

	if _, err := c.Get(ctx, loader); err == nil {
		t.Fatal("harus error")
	}
	gagal = false
	if v, err := c.Get(ctx, loader); err != nil || v != 7 {
		t.Errorf("setelah pulih = %v, %v; mau 7, nil", v, err)
	}
}

func TestTTLNolSelaluLoad(t *testing.T) {
	c := New[int](0)
	calls := 0
	loader := func(context.Context) (int, error) {
		calls++
		return calls, nil
	}
	ctx := context.Background()
	for i := 0; i < 3; i++ {
		if _, err := c.Get(ctx, loader); err != nil {
			t.Fatal(err)
		}
	}
	if calls != 3 {
		t.Errorf("ttl 0: loader %d kali, mau 3", calls)
	}
}

func TestReset(t *testing.T) {
	c := New[int](time.Hour)
	calls := 0
	loader := func(context.Context) (int, error) {
		calls++
		return calls, nil
	}
	ctx := context.Background()

	c.Get(ctx, loader)
	c.Reset()
	c.Get(ctx, loader)

	if calls != 2 {
		t.Errorf("setelah Reset loader %d kali, mau 2", calls)
	}
}

// TestAntiStampede: 50 goroutine bersamaan, loader tetap 1x.
func TestAntiStampede(t *testing.T) {
	c := New[int](time.Minute)
	var calls atomic.Int32
	loader := func(context.Context) (int, error) {
		calls.Add(1)
		time.Sleep(20 * time.Millisecond)
		return 1, nil
	}

	var wg sync.WaitGroup
	for i := 0; i < 50; i++ {
		wg.Add(1)
		go func() {
			defer wg.Done()
			if _, err := c.Get(context.Background(), loader); err != nil {
				t.Error(err)
			}
		}()
	}
	wg.Wait()

	if got := calls.Load(); got != 1 {
		t.Errorf("loader dipanggil %d kali, mau 1", got)
	}
}
